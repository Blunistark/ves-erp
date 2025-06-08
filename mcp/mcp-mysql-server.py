#!/usr/bin/env python3
"""
MCP Server for AMPPS MySQL Database Operations
Provides tools to query and manage the ERP database through MCP protocol.
"""

import asyncio
import json
import sys
from typing import Any, Dict, List, Optional
import pymysql
import logging

# MCP imports
from mcp.server import Server
from mcp.server.models import InitializationOptions
from mcp.server.stdio import stdio_server
from mcp.types import (
    Resource,
    Tool,
    TextContent,
    ImageContent,
    EmbeddedResource,
    LoggingLevel
)
from mcp.server import NotificationOptions

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Database configuration
DB_CONFIG = {
    'host': 'localhost',
    'port': 3306,
    'user': 'mcp_user',
    'password': 'mcp_password',
    'database': 'ves-admin',
    'charset': 'utf8mb4',
    'autocommit': True
}

app = Server("mysql-erp-server")

class DatabaseManager:
    """Manages database connections and operations"""
    
    def __init__(self):
        self.connection = None
    
    def connect(self) -> bool:
        """Establish database connection"""
        try:
            self.connection = pymysql.connect(**DB_CONFIG)
            logger.info("Database connection established")
            return True
        except Exception as e:
            logger.error(f"Database connection failed: {e}")
            return False
    
    def disconnect(self):
        """Close database connection"""
        if self.connection:
            self.connection.close()
            self.connection = None
            logger.info("Database connection closed")
    
    def execute_query(self, query: str, params: Optional[List] = None) -> Dict[str, Any]:
        """Execute a database query"""
        try:
            with self.connection.cursor(pymysql.cursors.DictCursor) as cursor:
                cursor.execute(query, params or [])
                
                query_upper = query.strip().upper()
                if query_upper.startswith(('SELECT', 'SHOW', 'DESCRIBE', 'DESC', 'EXPLAIN')):
                    results = cursor.fetchall()
                    return {
                        "success": True,
                        "data": results,
                        "row_count": len(results)
                    }
                else:
                    self.connection.commit()
                    return {
                        "success": True,
                        "message": f"Query executed successfully. Rows affected: {cursor.rowcount}",
                        "rows_affected": cursor.rowcount
                    }
        except Exception as e:
            logger.error(f"Query execution failed: {e}")
            return {
                "success": False,
                "error": str(e)
            }

# Initialize database manager
db_manager = DatabaseManager()

@app.list_tools()
async def handle_list_tools() -> List[Tool]:
    """List available tools"""
    return [
        Tool(
            name="execute_sql",
            description="Execute a SQL query on the ERP database",
            inputSchema={
                "type": "object",
                "properties": {
                    "query": {
                        "type": "string",
                        "description": "SQL query to execute"
                    },
                    "params": {
                        "type": "array",
                        "items": {"type": "string"},
                        "description": "Optional parameters for prepared statements"
                    }
                },
                "required": ["query"]
            }
        ),
        Tool(
            name="list_tables",
            description="List all tables in the ERP database",
            inputSchema={
                "type": "object",
                "properties": {}
            }
        ),
        Tool(
            name="describe_table",
            description="Get the structure of a specific table",
            inputSchema={
                "type": "object",
                "properties": {
                    "table_name": {
                        "type": "string",
                        "description": "Name of the table to describe"
                    }
                },
                "required": ["table_name"]
            }
        ),
        Tool(
            name="table_data",
            description="Get sample data from a table",
            inputSchema={
                "type": "object",
                "properties": {
                    "table_name": {
                        "type": "string",
                        "description": "Name of the table"
                    },
                    "limit": {
                        "type": "integer",
                        "description": "Number of rows to return (default: 10)",
                        "default": 10
                    }
                },
                "required": ["table_name"]
            }
        ),        Tool(
            name="student_search",
            description="Search for students by name, ID, or class",
            inputSchema={
                "type": "object",
                "properties": {
                    "search_term": {
                        "type": "string",
                        "description": "Search term (name, ID, etc.)"
                    },
                    "search_type": {
                        "type": "string",
                        "enum": ["name", "id", "class", "section"],
                        "description": "Type of search to perform"
                    }
                },
                "required": ["search_term", "search_type"]
            }
        )
    ]

@app.call_tool()
async def handle_call_tool(name: str, arguments: Dict[str, Any]) -> List[TextContent]:
    """Handle tool calls"""
    
    # Ensure database connection
    if not db_manager.connection:
        if not db_manager.connect():
            return [TextContent(
                type="text",
                text="Failed to connect to database"
            )]
    
    try:
        if name == "execute_sql":
            query = arguments.get("query", "")
            params = arguments.get("params", [])
            
            if not query:
                return [TextContent(
                    type="text",
                    text="Query parameter is required"
                )]
            
            result = db_manager.execute_query(query, params)
            return [TextContent(
                type="text",
                text=json.dumps(result, indent=2, default=str)
            )]
        
        elif name == "list_tables":
            query = "SHOW TABLES"
            result = db_manager.execute_query(query)
            return [TextContent(
                type="text",
                text=json.dumps(result, indent=2, default=str)
            )]
        
        elif name == "describe_table":
            table_name = arguments.get("table_name", "")
            
            if not table_name:
                return [TextContent(
                    type="text",
                    text="Table name is required"
                )]
            
            query = f"DESCRIBE `{table_name}`"
            result = db_manager.execute_query(query)
            return [TextContent(
                type="text",
                text=json.dumps(result, indent=2, default=str)
            )]
        
        elif name == "table_data":
            table_name = arguments.get("table_name", "")
            limit = arguments.get("limit", 10)
            
            if not table_name:
                return [TextContent(
                    type="text",
                    text="Table name is required"
                )]
            
            query = f"SELECT * FROM `{table_name}` LIMIT %s"
            result = db_manager.execute_query(query, [limit])
            return [TextContent(
                type="text",
                text=json.dumps(result, indent=2, default=str)
            )]
        
        elif name == "student_search":
            search_term = arguments.get("search_term", "")
            search_type = arguments.get("search_type", "")
            
            if not search_term or not search_type:
                return [TextContent(
                    type="text",
                    text="Both search_term and search_type are required"
                )]
            
            if search_type == "name":
                query = """
                    SELECT s.user_id, u.full_name, c.name as class_name, sec.name as section_name, s.roll_number
                    FROM students s
                    JOIN users u ON s.user_id = u.id
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN sections sec ON s.section_id = sec.id
                    WHERE u.full_name LIKE %s
                    ORDER BY u.full_name
                """
                params = [f"%{search_term}%"]
            
            elif search_type == "id":
                query = """
                    SELECT s.user_id, u.full_name, c.name as class_name, sec.name as section_name, s.roll_number
                    FROM students s
                    JOIN users u ON s.user_id = u.id
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN sections sec ON s.section_id = sec.id
                    WHERE s.user_id = %s OR s.roll_number = %s
                """
                params = [search_term, search_term]
            
            elif search_type == "class":
                query = """
                    SELECT s.user_id, u.full_name, c.name as class_name, sec.name as section_name, s.roll_number
                    FROM students s
                    JOIN users u ON s.user_id = u.id
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN sections sec ON s.section_id = sec.id
                    WHERE c.name LIKE %s
                    ORDER BY c.name, u.full_name
                """
                params = [f"%{search_term}%"]
            
            elif search_type == "section":
                query = """
                    SELECT s.user_id, u.full_name, c.name as class_name, sec.name as section_name, s.roll_number
                    FROM students s
                    JOIN users u ON s.user_id = u.id
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN sections sec ON s.section_id = sec.id
                    WHERE sec.name LIKE %s
                    ORDER BY sec.name, u.full_name
                """
                params = [f"%{search_term}%"]
            
            else:                return [TextContent(
                    type="text",
                    text="Invalid search_type. Must be one of: name, id, class, section"
                )]
            
            result = db_manager.execute_query(query, params)
            return [TextContent(
                type="text",
                text=json.dumps(result, indent=2, default=str)
            )]
        
        else:
            return [TextContent(
                type="text",
                text=f"Unknown tool: {name}"
            )]
    
    except Exception as e:
        logger.error(f"Tool execution failed: {e}")
        return [TextContent(
            type="text",
            text=f"Error executing tool {name}: {str(e)}"
        )]

async def main():
    """Main entry point"""
    try:
        # Connect to database
        if not db_manager.connect():
            logger.error("Failed to establish database connection")
            sys.exit(1)
        
        # Run the server
        async with stdio_server() as (read_stream, write_stream):
            await app.run(
                read_stream,
                write_stream,
                InitializationOptions(
                    server_name="mysql-erp-server",
                    server_version="1.0.0",
                    capabilities=app.get_capabilities(
                        notification_options=NotificationOptions(),
                        experimental_capabilities={},
                    ),
                ),
            )
    finally:
        db_manager.disconnect()

if __name__ == "__main__":
    asyncio.run(main())
