# MCP API Checker

An MCP (Model Context Protocol) server that provides API checking functionality. This server allows you to make HTTP requests to API endpoints and get detailed responses including status codes, headers, response data, and response times.

## Features

- **API GET Requests**: Make GET requests to any API endpoint
- **API POST Requests**: Make POST requests with custom body data
- **General API Check**: Make requests with any HTTP method (GET, POST, PUT, DELETE)
- **Custom Headers**: Add custom headers to your requests
- **Response Analysis**: Get detailed response information including:
  - HTTP status code and status text
  - Response headers
  - Response data (JSON or text)
  - Response time in milliseconds
- **Error Handling**: Proper error handling for network issues and invalid responses

## Installation

1. Clone or download this repository
2. Install dependencies:
   ```bash
   npm install
   ```
3. Build the project:
   ```bash
   npm run build
   ```

## Usage

### Running the Server

To run the MCP server:

```bash
npm start
```

Or directly:

```bash
node build/index.js
```

### Available Tools

#### 1. `api_get`
Make a GET request to an API endpoint.

**Parameters:**
- `url` (string, required): The API URL to check
- `headers` (object, optional): Optional headers to include in the request

#### 2. `api_post`
Make a POST request to an API endpoint.

**Parameters:**
- `url` (string, required): The API URL to send POST request to
- `body` (any, optional): Request body (JSON, string, or FormData)
- `headers` (object, optional): Optional headers to include in the request

#### 3. `api_check`
Check an API endpoint with customizable HTTP method.

**Parameters:**
- `url` (string, required): The API URL to check
- `method` (string, optional): HTTP method to use (GET, POST, PUT, DELETE) - defaults to GET
- `body` (any, optional): Request body for POST/PUT requests
- `headers` (object, optional): Optional headers to include in the request

### Example Usage with Claude Desktop

1. Configure Claude Desktop by adding this server to your `claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "api-checker": {
      "command": "node",
      "args": ["D:\\path\\to\\your\\project\\build\\index.js"]
    }
  }
}
```

2. Restart Claude Desktop

3. You can now use commands like:
   - "Check the API at https://jsonplaceholder.typicode.com/posts/1"
   - "Make a POST request to https://httpbin.org/post with some test data"
   - "Get the weather from https://api.openweathermap.org/data/2.5/weather?q=London&appid=YOUR_API_KEY"

## Development

### Project Structure

```
mcp-api/
├── src/
│   └── index.ts          # Main server implementation
├── build/
│   └── index.js          # Compiled JavaScript
├── .vscode/
│   ├── mcp.json          # MCP server configuration
│   └── tasks.json        # VS Code tasks
├── package.json          # Project dependencies and scripts
├── tsconfig.json         # TypeScript configuration
└── README.md            # This file
```

### Building

```bash
npm run build
```

### Development Mode

For development with automatic rebuilding:

```bash
npm run dev
```

## Technical Details

- Built with TypeScript
- Uses the `@modelcontextprotocol/sdk` for MCP integration
- Uses `zod` for schema validation
- Supports stdio transport for communication
- Handles JSON and text responses
- Includes comprehensive error handling and timeout management

## API Response Format

The server returns formatted responses that include:

```
✅ API Response

Status: 200 OK
Response Time: 245ms

Headers:
  content-type: application/json
  content-length: 292

Response Data:
{
  "userId": 1,
  "id": 1,
  "title": "sunt aut facere repellat provident occaecati excepturi optio reprehenderit",
  "body": "quia et suscipit..."
}
```

## License

MIT License - feel free to use this project for your own API testing needs.
