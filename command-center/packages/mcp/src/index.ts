#!/usr/bin/env node
import process from 'node:process';

import { resolveProjectRoot } from '@command-center/tracker-core/node';
import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import { CallToolRequestSchema, ListToolsRequestSchema } from '@modelcontextprotocol/sdk/types.js';

import { handleTool, listTools } from './tools.js';

const main = async () => {
  const projectRoot = await resolveProjectRoot();
  const server = new Server(
    {
      name: 'command-center',
      version: '1.0.0',
    },
    {
      capabilities: {
        tools: {},
      },
    },
  );

  server.setRequestHandler(ListToolsRequestSchema, async () => ({
    tools: listTools(),
  }));

  server.setRequestHandler(CallToolRequestSchema, async (request) =>
    handleTool(request.params.name, request.params.arguments ?? {}, { projectRoot }),
  );

  const transport = new StdioServerTransport();
  await server.connect(transport);
  process.stderr.write('Command Center MCP server running on stdio\n');
};

main().catch((error) => {
  process.stderr.write(`${error instanceof Error ? error.message : 'MCP server error'}\n`);
  process.exit(1);
});
