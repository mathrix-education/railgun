import * as core from '@actions/core';
import * as exec from '@actions/exec';
import { existsSync, readFileSync } from 'fs';
import { execGetOutput } from './utils';

type Cachable = string | number | boolean;
const cache: Map<string, Cachable> = new Map();

async function caching<T extends Cachable>(name: string, callback: () => Promise<T>): Promise<T> {
  if (!cache.has(name)) {
    cache.set(name, await callback());
  }

  return (cache.get(name) as unknown) as T;
}

export async function getVersion(): Promise<string> {
  return caching<string>('version', async () => {
    if (core.getInput('version') !== '') {
      return core.getInput('version');
    }

    const wellKnownFiles = ['composer.json', 'package.json'];
    const file = wellKnownFiles.find(file => existsSync(file));

    if (file === undefined) {
      throw new Error();
    }

    const data: { version: string } = JSON.parse(readFileSync(file).toString());

    return data.version;
  });
}

export async function getBuildNumber(): Promise<string> {
  return caching<string>('build', async () => {
    return process.env.GITHUB_RUN_NUMBER ?? '0000';
  });
}

export async function getRef(): Promise<string> {
  return caching<string>('ref', async () => {
    return execGetOutput('git', ['rev-parse', '--abbrev-ref', 'HEAD']);
  });
}

export async function getSha(): Promise<string> {
  return caching<string>('sha', async () => {
    return execGetOutput('git', ['rev-parse', 'HEAD']);
  });
}

export async function isTag(): Promise<boolean> {
  return caching<boolean>('isTag', async () => {
    const code = await exec.exec('git', ['show-ref', '--verify', `refs/tags/${await getRef()}`], {
      silent: true,
      ignoreReturnCode: true,
    });

    return code === 0;
  });
}

export async function isBranch(): Promise<boolean> {
  return caching<boolean>('isTag', async () => {
    const code = await exec.exec('git', ['show-ref', '--verify', `refs/heads/${await getRef()}`], {
      silent: true,
      ignoreReturnCode: true,
    });

    return code === 0;
  });
}

export async function getParentBranch(): Promise<string> {
  switch (await getRef()) {
    case 'master':
      return 'no-parent';
    case 'dev':
      return 'master';
    default:
      return 'dev';
  }
}
