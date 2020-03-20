import * as exec from '@actions/exec';
import * as im from '@actions/exec/lib/interfaces';

export async function execGetOutput(commandLine: string, args?: string[], options?: im.ExecOptions): Promise<string> {
  let output = '';

  options = {
    ...options,
    ...{
      silent: true,
      listeners: {
        stdout: (stdout): void => {
          // We know for sure that the output will be set since we use await
          output = stdout.toString().trim();
        },
      },
    },
  };

  await exec.exec(commandLine, args, options);

  return output;
}
