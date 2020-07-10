#!/usr/bin/env bash
version_from_tag=${GITHUB_REF/refs\/tags\//}
version_composer_json=$(jq -r .version < composer.json)

if [[ "$version_from_tag" != "$version_composer_json" ]]; then
  echo "::error ::Expected $version_from_tag but got $version_composer_json in composer.json"
fi
