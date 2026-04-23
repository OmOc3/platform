#!/usr/bin/env node
import process from 'node:process';

import { resolveProjectRoot } from '@command-center/tracker-core/node';

import { handleTool } from './tools.js';

const positionalArrayArgs = new Set(['tags', 'acceptance_criteria', 'constraints', 'context_files', 'reference_docs', 'depends_on', 'permissions']);

const toSnakeCase = (value: string) => value.replace(/-/g, '_');

const coerceValue = (key: string, value: string) => {
  if (positionalArrayArgs.has(key)) {
    return value.split(',').map((item) => item.trim()).filter(Boolean);
  }

  if (/^-?\d+$/.test(value)) {
    return Number(value);
  }

  return value;
};

const parseArgs = (argv: string[]) => {
  const command = argv[2];
  const named: Record<string, unknown> = {};
  const positional: string[] = [];

  for (let index = 3; index < argv.length; index += 1) {
    const token = argv[index];

    if (token.startsWith('--')) {
      const key = toSnakeCase(token.slice(2));
      const next = argv[index + 1];

      if (!next || next.startsWith('--')) {
        named[key] = true;
        continue;
      }

      named[key] = coerceValue(key, next);
      index += 1;
      continue;
    }

    positional.push(token);
  }

  return { command, named, positional };
};

const positionalToArgs = (command: string, positional: string[], named: Record<string, unknown>) => {
  const args = { ...named };

  switch (command) {
    case 'get-task-context':
    case 'get-task-summary':
    case 'get-task-history':
    case 'start-task':
    case 'reset-task':
    case 'unblock-task':
      if (positional[0]) args.task_id = positional[0];
      break;
    case 'complete-task':
      if (positional[0]) args.task_id = positional[0];
      if (positional[1]) args.summary = positional[1];
      break;
    case 'approve-task':
      if (positional[0]) args.task_id = positional[0];
      if (positional[1]) args.feedback = positional[1];
      break;
    case 'reject-task':
      if (positional[0]) args.task_id = positional[0];
      if (positional[1]) args.feedback = positional[1];
      break;
    case 'block-task':
      if (positional[0]) args.task_id = positional[0];
      if (positional[1]) args.reason = positional[1];
      break;
    case 'get-milestone-overview':
    case 'add-milestone-note':
    case 'set-milestone-dates':
    case 'update-drift':
      if (positional[0]) args.milestone_id = positional[0];
      if (command === 'add-milestone-note' && positional[1]) args.note = positional[1];
      if (command === 'update-drift' && positional[1]) args.drift_days = Number(positional[1]);
      break;
    case 'create-milestone':
      if (positional[0]) args.id = positional[0];
      if (positional[1]) args.title = positional[1];
      break;
    case 'add-milestone-task':
      if (positional[0]) args.milestone_id = positional[0];
      if (positional[1]) args.label = positional[1];
      break;
    case 'register-agent':
      if (positional[0]) args.agent_id = positional[0];
      if (positional[1]) args.name = positional[1];
      if (positional[2]) args.type = positional[2];
      break;
    default:
      break;
  }

  return args;
};

const main = async () => {
  const { command, named, positional } = parseArgs(process.argv);

  if (!command) {
    process.stderr.write('Usage: command-center <command> [...args]\n');
    process.exit(1);
  }

  const projectRoot = await resolveProjectRoot();
  const toolName = toSnakeCase(command);
  const args = positionalToArgs(command, positional, named);
  const result = await handleTool(toolName, args, { projectRoot });
  const text = result.content[0]?.text ?? '';

  if (result.isError) {
    process.stderr.write(`${text}\n`);
    process.exit(1);
  }

  process.stdout.write(`${text}\n`);
};

main().catch((error) => {
  process.stderr.write(`${error instanceof Error ? error.message : 'CLI error'}\n`);
  process.exit(1);
});
