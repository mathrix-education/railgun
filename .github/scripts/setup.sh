#!/usr/bin/env bash
# Setup the pipeline configuration based on the current context
# We will use the GITHUB_REF environment variable to setup most of the values

# Configuration
google_cloud_project="mathrix-education"
google_cloud_repository="eu.gcr.io"
google_cloud_image_name="education-api"

root="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null 2>&1 && pwd)"
# shellcheck source=actions.sh
source "$root/actions.sh"

# Git-related
if [[ -z "$GITHUB_SHA" ]]; then
  sha=$(git rev-parse HEAD)
else
  sha="$GITHUB_SHA"
fi

short_sha=${sha:0:7}

# Setup environment
if [[ "$GITHUB_REF" == "refs/tags"* ]]; then
  environment="production"
else
  environment="edge"
fi

if [[ "$environment" == "production" ]]; then
  url="https://mathrix.fr/graphql"
else
  url="https://$environment.mathrix.tech/graphql"
fi

# Docker Image on the Google Cloud Repository
image_name="$google_cloud_repository/$google_cloud_project/$google_cloud_image_name"

image_tag=$(
  gcloud container images list-tags \
    "$image_name" \
    "--filter=$short_sha" \
    --format=json \
    --limit=1 \
    | jq -r .[0].tags[0]
)

# If the image exists
[[ "$image_tag" != "null" ]] && image_exists="1" || image_exists="0"

# Setup versions
if [[ $image_exists == "1" ]]; then
  version=$(echo "$image_tag" | sed -r 's/([0-9]+\.[0-9]+\.[0-9]+).*/\1/')
  counter=$(echo "$image_tag" | sed -r 's/.*[0-9]+\.[0-9]+\.[0-9]+\-([0-9]+)\..*/\1/')
else
  version=$(jq -r .version < "$root/../../composer.json")
  counter="${GITHUB_RUN_NUMBER:-0000}"
  image_tag="$version-$counter.$short_sha"
fi

semver="$version+$counter.$short_sha"
image="$image_name:$image_tag"

# Outputs
set_output "environment" "$environment"
set_output "url" "$url"
set_output "semver" "$semver"
set_output "image" "$image"
set_output "image_exists" "$image_exists"
set_output "skip_lint" "$image_exists"
set_output "skip_test" "$image_exists"
set_output "skip_build" "$image_exists"
