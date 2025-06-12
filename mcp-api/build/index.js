#!/usr/bin/env node
import { McpServer } from "@modelcontextprotocol/sdk/server/mcp.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import { z } from "zod";
// Session storage for authentication cookies
let sessionStore = {};
// Helper function to make HTTP requests with session management
async function makeHttpRequest(url, method = 'GET', body, headers = {}) {
    const startTime = Date.now();
    try {
        const requestHeaders = {
            'User-Agent': 'MCP-API-Checker/1.0',
            'Accept': 'application/json, text/plain, */*',
            ...headers
        };
        // Add stored cookies for session management
        if (Object.keys(sessionStore).length > 0) {
            const cookieString = Object.entries(sessionStore)
                .map(([key, value]) => `${key}=${value}`)
                .join('; ');
            requestHeaders['Cookie'] = cookieString;
        }
        // Prepare request body
        let requestBody;
        if (body !== undefined && method !== 'GET' && method !== 'DELETE') {
            if (typeof body === 'object') {
                requestBody = JSON.stringify(body);
                if (!requestHeaders['Content-Type']) {
                    requestHeaders['Content-Type'] = 'application/json';
                }
            }
            else {
                requestBody = String(body);
            }
        }
        const response = await fetch(url, {
            method,
            headers: requestHeaders,
            body: requestBody
        });
        const responseTime = Date.now() - startTime;
        // Extract and store session cookies from response
        const setCookieHeaders = response.headers.get('set-cookie');
        if (setCookieHeaders) {
            const cookies = setCookieHeaders.split(',');
            cookies.forEach(cookie => {
                const [nameValue] = cookie.split(';');
                const [name, value] = nameValue.split('=');
                if (name && value) {
                    sessionStore[name.trim()] = value.trim();
                }
            });
        }
        // Get response headers
        const responseHeaders = {};
        response.headers.forEach((value, key) => {
            responseHeaders[key] = value;
        });
        // Try to parse response as JSON, fall back to text
        let data;
        const contentType = response.headers.get('content-type') || '';
        try {
            if (contentType.includes('application/json')) {
                data = await response.json();
            }
            else {
                data = await response.text();
            }
        }
        catch (error) {
            data = `Failed to parse response: ${error instanceof Error ? error.message : 'Unknown error'}`;
        }
        // Check if response indicates authentication is required
        const authenticated = !(response.status === 403 ||
            response.status === 401 ||
            (response.status === 302 && (responseHeaders['location']?.includes('login') ||
                responseHeaders['location']?.includes('index.php'))) ||
            (typeof data === 'object' && data?.success === false &&
                data?.message?.toLowerCase().includes('unauthorized')));
        return {
            status: response.status,
            statusText: response.statusText,
            headers: responseHeaders,
            data,
            responseTime,
            authenticated
        };
    }
    catch (error) {
        const responseTime = Date.now() - startTime;
        return {
            status: 0,
            statusText: 'Network Error',
            headers: {},
            data: `Request failed: ${error instanceof Error ? error.message : 'Unknown error'}`,
            responseTime,
            authenticated: false
        };
    }
}
// Enhanced auto-authentication function for the ERP system
async function attemptAutoLogin(baseUrl, targetUrl) {
    // Determine which portal to try based on the target URL
    const getPortalFromUrl = (url) => {
        if (url.includes('/admin/'))
            return 'admin';
        if (url.includes('/teachers/'))
            return 'teacher';
        if (url.includes('/student/'))
            return 'student';
        return 'unknown';
    };
    const targetPortal = targetUrl ? getPortalFromUrl(targetUrl) : 'unknown';
    // Enhanced login endpoints with multiple credential sets for VES ERP
    const loginEndpoints = [
        {
            portal: 'admin',
            path: '/erp/admin/index.php',
            loginAction: '/erp/admin/index.php',
            credentials: [
                { email: 'vinodh.schl.edu@gmail.com', password: 'Ves@11135' }
            ]
        },
        {
            portal: 'teacher',
            path: '/erp/teachers/index.php',
            loginAction: '/erp/teachers/index.php',
            credentials: [
                { identifier: 'VES2025T006', password: '300171987' }
            ]
        },
        {
            portal: 'student',
            path: '/erp/student/index.php',
            loginAction: '/erp/student/index.php',
            credentials: [
                { sats_number: '253305802', dob: '23/07/2018' }
            ]
        }
    ];
    const attempts = [];
    // Try target portal first if known
    const orderedEndpoints = targetPortal !== 'unknown'
        ? [
            ...loginEndpoints.filter(e => e.portal === targetPortal),
            ...loginEndpoints.filter(e => e.portal !== targetPortal)
        ]
        : loginEndpoints;
    for (const endpoint of orderedEndpoints) {
        for (const credentials of endpoint.credentials) {
            try {
                const loginUrl = baseUrl + endpoint.path;
                // First get the login page to extract any tokens/csrf
                const loginPageResponse = await makeHttpRequest(loginUrl, 'GET');
                if (loginPageResponse.status === 200) {
                    let csrfToken = '';
                    // Extract CSRF token from various possible formats
                    if (typeof loginPageResponse.data === 'string') {
                        const csrfPatterns = [
                            /name=["\']csrf_token["\']\s+value=["\']([^"\']+)["\']/,
                            /name=["\']_token["\']\s+value=["\']([^"\']+)["\']/,
                            /<input[^>]*name=["\']csrf[^"\']*["\']\s+value=["\']([^"\']+)["\']/,
                            /csrf_token["\']?\s*:\s*["\']([^"\']+)["\']/
                        ];
                        for (const pattern of csrfPatterns) {
                            const match = loginPageResponse.data.match(pattern);
                            if (match) {
                                csrfToken = match[1];
                                break;
                            }
                        }
                    } // Prepare login data based on portal type and analyze form structure
                    const formData = new URLSearchParams();
                    // Extract form field names from the login page HTML and detect form action
                    let formFields = {};
                    let formAction = endpoint.loginAction;
                    if (typeof loginPageResponse.data === 'string') {
                        // Extract form action if present
                        const formActionPattern = /<form[^>]*action=["']([^"']*?)["'][^>]*>/i;
                        const actionMatch = loginPageResponse.data.match(formActionPattern);
                        if (actionMatch && actionMatch[1]) {
                            formAction = actionMatch[1].startsWith('http') ? actionMatch[1] : baseUrl + actionMatch[1];
                        }
                        // Look for input field patterns in the HTML
                        const inputPatterns = [
                            /<input[^>]*name=["']([^"']+)["'][^>]*>/g,
                            /<input[^>]*name=([^\s>]+)[^>]*>/g
                        ];
                        for (const pattern of inputPatterns) {
                            let match;
                            while ((match = pattern.exec(loginPageResponse.data)) !== null) {
                                const fieldName = match[1].replace(/['"]/g, '');
                                if (!formFields[fieldName] && !['submit', 'reset', 'button'].includes(fieldName.toLowerCase())) {
                                    formFields[fieldName] = '';
                                }
                            }
                        }
                        // Also check for login form patterns specific to ERP
                        const erpFormIndicators = [
                            'login_form', 'user_login', 'admin_login', 'teacher_login', 'student_login'
                        ];
                        for (const indicator of erpFormIndicators) {
                            if (loginPageResponse.data.includes(indicator)) {
                                // This is likely a login form page
                                break;
                            }
                        }
                    }
                    // Add portal-specific form fields with intelligent field mapping
                    if (endpoint.portal === 'admin') {
                        // Map credentials to form fields
                        Object.entries(credentials).forEach(([key, value]) => {
                            // Try to find matching field names in the form
                            const possibleFields = Object.keys(formFields);
                            let fieldName = key;
                            if (key === 'email') {
                                fieldName = possibleFields.find(f => f.toLowerCase().includes('email') ||
                                    f.toLowerCase().includes('user') ||
                                    f.toLowerCase() === 'email') || 'email';
                            }
                            else if (key === 'password') {
                                fieldName = possibleFields.find(f => f.toLowerCase().includes('password') ||
                                    f.toLowerCase().includes('pass')) || 'password';
                            }
                            formData.append(fieldName, value);
                        });
                    }
                    else if (endpoint.portal === 'teacher') {
                        Object.entries(credentials).forEach(([key, value]) => {
                            const possibleFields = Object.keys(formFields);
                            let fieldName = key;
                            if (key === 'identifier') {
                                fieldName = possibleFields.find(f => f.toLowerCase().includes('identifier') ||
                                    f.toLowerCase().includes('id') ||
                                    f.toLowerCase().includes('user') ||
                                    f.toLowerCase() === 'teacher_id') || 'identifier';
                            }
                            else if (key === 'password') {
                                fieldName = possibleFields.find(f => f.toLowerCase().includes('password') ||
                                    f.toLowerCase().includes('pass')) || 'password';
                            }
                            formData.append(fieldName, value);
                        });
                    }
                    else if (endpoint.portal === 'student') {
                        Object.entries(credentials).forEach(([key, value]) => {
                            const possibleFields = Object.keys(formFields);
                            let fieldName = key;
                            if (key === 'sats_number') {
                                fieldName = possibleFields.find(f => f.toLowerCase().includes('sats') ||
                                    f.toLowerCase().includes('student') ||
                                    f.toLowerCase().includes('number') ||
                                    f.toLowerCase() === 'student_id') || 'sats_number';
                            }
                            else if (key === 'dob') {
                                fieldName = possibleFields.find(f => f.toLowerCase().includes('dob') ||
                                    f.toLowerCase().includes('birth') ||
                                    f.toLowerCase().includes('date')) || 'dob';
                            }
                            formData.append(fieldName, value);
                        });
                    }
                    // Add CSRF token if found
                    if (csrfToken) {
                        formData.append('csrf_token', csrfToken);
                    }
                    // Add common login indicators
                    formData.append('login', '1');
                    formData.append('submit', 'Login');
                    // Try alternative submit field names that might be used
                    const submitFields = ['login_submit', 'submit_login', 'btn_login', 'loginBtn'];
                    submitFields.forEach(field => {
                        if (Object.keys(formFields).includes(field)) {
                            formData.append(field, '1');
                        }
                    });
                    // Attempt login to the detected or default form action
                    const loginResponse = await makeHttpRequest(formAction, 'POST', formData.toString(), {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Referer': loginUrl,
                        'X-Requested-With': 'XMLHttpRequest'
                    });
                    const attempt = {
                        portal: endpoint.portal,
                        url: loginUrl,
                        formAction: formAction,
                        credentials: Object.keys(credentials),
                        status: loginResponse.status,
                        hasError: typeof loginResponse.data === 'string' &&
                            (loginResponse.data.includes('error') ||
                                loginResponse.data.includes('Invalid') ||
                                loginResponse.data.includes('incorrect') ||
                                loginResponse.data.includes('failed') ||
                                loginResponse.data.includes('wrong'))
                    };
                    attempts.push(attempt);
                    // Enhanced login success detection
                    const isSuccess = loginResponse.status === 302 ||
                        (loginResponse.status === 200 && !attempt.hasError) ||
                        (typeof loginResponse.data === 'object' && loginResponse.data?.success === true) ||
                        (typeof loginResponse.data === 'string' &&
                            (loginResponse.data.includes('dashboard') ||
                                loginResponse.data.includes('welcome') ||
                                loginResponse.data.includes('logout')));
                    if (isSuccess) {
                        // If we got a redirect, follow it to establish the session properly
                        if (loginResponse.status === 302 && loginResponse.headers['location']) {
                            const redirectUrl = loginResponse.headers['location'].startsWith('http')
                                ? loginResponse.headers['location']
                                : baseUrl + loginResponse.headers['location'];
                            await makeHttpRequest(redirectUrl, 'GET');
                        }
                        return { success: true, userType: endpoint.portal, attempts };
                    }
                }
            }
            catch (error) {
                attempts.push({
                    portal: endpoint.portal,
                    error: error instanceof Error ? error.message : 'Unknown error'
                });
                continue;
            }
        }
    }
    return { success: false, attempts };
}
// Format response for display with authentication status
function formatResponse(response, authAttempts) {
    const statusColor = response.status >= 200 && response.status < 300 ? '✅' :
        response.status >= 400 ? '❌' : '⚠️';
    const authStatus = response.authenticated === false ? '🔒 **AUTHENTICATION REQUIRED**\n' : '';
    let authDetails = '';
    if (authAttempts && authAttempts.length > 0) {
        authDetails = `\n**Authentication Attempts:**\n${authAttempts.map(attempt => `  ${attempt.portal}: ${attempt.status || 'failed'} ${attempt.error ? `(${attempt.error})` : ''}`).join('\n')}\n`;
    }
    return `${authStatus}${statusColor} **API Response**

**Status:** ${response.status} ${response.statusText}
**Response Time:** ${response.responseTime}ms
**Authenticated:** ${response.authenticated !== false ? '✅ Yes' : '❌ No'}
${authDetails}
**Headers:**
${Object.entries(response.headers).map(([key, value]) => `  ${key}: ${value}`).join('\n') || '  (no headers)'}

**Response Data:**
\`\`\`json
${typeof response.data === 'object' ? JSON.stringify(response.data, null, 2) : response.data}
\`\`\``;
}
// Create server instance
const server = new McpServer({
    name: "api-checker",
    version: "1.0.0",
    capabilities: {
        tools: {},
    },
});
// Register tools using the raw shape format
server.tool("api_get", "Make a GET request to an API endpoint with automatic authentication", {
    url: z.string().url().describe("The API URL to check"),
    headers: z.record(z.string()).optional().describe("Optional headers to include in the request"),
    autoAuth: z.boolean().optional().default(true).describe("Automatically attempt authentication if required")
}, async ({ url, headers = {}, autoAuth = true }) => {
    let response = await makeHttpRequest(url, 'GET', undefined, headers);
    let authAttempts = [];
    // If authentication is required and autoAuth is enabled, try to authenticate
    if (autoAuth && response.authenticated === false) {
        const baseUrl = new URL(url).origin;
        const loginResult = await attemptAutoLogin(baseUrl, url);
        authAttempts = loginResult.attempts;
        if (loginResult.success) {
            // Retry the request with authentication
            response = await makeHttpRequest(url, 'GET', undefined, headers);
        }
    }
    return {
        content: [
            {
                type: "text",
                text: formatResponse(response, authAttempts),
            },
        ],
    };
});
server.tool("api_post", "Make a POST request to an API endpoint with automatic authentication", {
    url: z.string().url().describe("The API URL to send POST request to"),
    body: z.any().optional().describe("Request body (JSON, string, or FormData)"),
    headers: z.record(z.string()).optional().describe("Optional headers to include in the request"),
    autoAuth: z.boolean().optional().default(true).describe("Automatically attempt authentication if required")
}, async ({ url, body, headers = {}, autoAuth = true }) => {
    let response = await makeHttpRequest(url, 'POST', body, headers);
    let authAttempts = [];
    // If authentication is required and autoAuth is enabled, try to authenticate
    if (autoAuth && response.authenticated === false) {
        const baseUrl = new URL(url).origin;
        const loginResult = await attemptAutoLogin(baseUrl, url);
        authAttempts = loginResult.attempts;
        if (loginResult.success) {
            // Retry the request with authentication
            response = await makeHttpRequest(url, 'POST', body, headers);
        }
    }
    return {
        content: [
            {
                type: "text",
                text: formatResponse(response, authAttempts),
            },
        ],
    };
});
server.tool("api_check", "Check an API endpoint with customizable HTTP method and automatic authentication", {
    url: z.string().url().describe("The API URL to check"),
    method: z.enum(['GET', 'POST', 'PUT', 'DELETE']).optional().describe("HTTP method to use (defaults to GET)"),
    body: z.any().optional().describe("Request body for POST/PUT requests"),
    headers: z.record(z.string()).optional().describe("Optional headers to include in the request"),
    autoAuth: z.boolean().optional().default(true).describe("Automatically attempt authentication if required")
}, async ({ url, method = 'GET', body, headers = {}, autoAuth = true }) => {
    let response = await makeHttpRequest(url, method, body, headers);
    let authAttempts = [];
    // If authentication is required and autoAuth is enabled, try to authenticate
    if (autoAuth && response.authenticated === false) {
        const baseUrl = new URL(url).origin;
        const loginResult = await attemptAutoLogin(baseUrl, url);
        authAttempts = loginResult.attempts;
        if (loginResult.success) {
            // Retry the request with authentication
            response = await makeHttpRequest(url, method, body, headers);
        }
    }
    return {
        content: [
            {
                type: "text",
                text: formatResponse(response, authAttempts),
            },
        ],
    };
});
async function main() {
    const transport = new StdioServerTransport();
    await server.connect(transport);
    console.error("MCP API Checker Server running on stdio");
}
main().catch((error) => {
    console.error("Fatal error in main():", error);
    process.exit(1);
});
