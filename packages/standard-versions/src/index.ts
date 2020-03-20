import * as core from '@actions/core';
import { getBuildNumber, getParentBranch, getRef, getSha, getVersion, isBranch, isTag } from './inputs';

try {
  (async (): Promise<void> => {
    const version = await getVersion();
    const build = await getBuildNumber();
    const ref = await getRef();
    const sha = await getSha();
    const shortSha = sha.substr(0, 7);
    const refSlug = ref.replace(/\./g, '-');

    // version
    core.setOutput('version', version);

    // branch
    if (await isBranch()) {
      core.setOutput('branch', await getRef());
      core.setOutput('parent_branch', await getParentBranch());
    }

    // docker
    if (await isTag()) {
      core.setOutput('docker', `${version}-${build}.${shortSha}`);
    } else if (await isBranch()) {
      core.setOutput('docker', `${version}-${build}.${refSlug}.${shortSha}`);
    }

    // semver
    if (await isTag()) {
      core.setOutput('semver', `${version}+${build}.${shortSha}`);
    } else if (await isBranch()) {
      core.setOutput('semver', `${version}+${build}.${refSlug}.${shortSha}`);
    }
  })();
} catch (exception) {
  core.setFailed(exception.message);
}
