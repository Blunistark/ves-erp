#!/usr/bin/env python3
"""
Test script for MCP MySQL Server
"""

import pymysql

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

def test_connection():
    """Test database connection and basic queries"""
    try:
        # Connect to database
        connection = pymysql.connect(**DB_CONFIG)
        print("✅ Database connection successful!")
        
        # Test SHOW TABLES
        with connection.cursor(pymysql.cursors.DictCursor) as cursor:
            cursor.execute("SHOW TABLES")
            tables = cursor.fetchall()
            print(f"✅ Found {len(tables)} tables in database")
            
            # Show first few tables
            print("\nFirst 5 tables:")
            for i, table in enumerate(tables[:5]):
                table_name = list(table.values())[0]
                print(f"  {i+1}. {table_name}")
        
        # Test a simple SELECT query
        with connection.cursor(pymysql.cursors.DictCursor) as cursor:
            cursor.execute("SELECT COUNT(*) as student_count FROM students")
            result = cursor.fetchone()
            print(f"✅ Student count: {result['student_count']}")
        
        connection.close()
        print("✅ Database connection closed successfully!")
        return True
        
    except Exception as e:
        print(f"❌ Database test failed: {e}")
        return False

if __name__ == "__main__":
    print("Testing MCP MySQL Server Database Connection...")
    print("=" * 50)
    
    if test_connection():
        print("\n🎉 All tests passed! MCP server should work correctly.")
    else:
        print("\n💥 Tests failed! Check your database configuration.")
