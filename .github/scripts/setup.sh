#!/usr/bin/env bash
# Setup the pipeline configuration based on the current context
# We will use the GITHUB_REF environment variable to setup most of the values

# Configuration
google_cloud_project="mathrix-education"
google_cloud_repository="europe-west1-docker.pkg.dev/mathrix-education/docker"
google_cloud_image_name="railgun"

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
deploy_force="0"
environment=$(git log -1 --format=%B | sed -n -E 's/.*deploy: ([a-z]+).*/\1/p')

if [[ -z $environment ]]; then
    branch=${GITHUB_REF/refs\/heads\//}

    if [[ $GITHUB_EVENT_NAME == "pull_request" ]]; then
        # This is a pull_request event and we we should be able to retrieve the branch name form the event JSON
        branch=$(jq -r .pull_request.head.ref "$GITHUB_EVENT_PATH")
    fi

    cmd="jq -r '.[\"$branch\"]' $root/../../environments.json"
    mapped_environment=$(eval "$cmd")

    if [[ "$mapped_environment" != "null" ]]; then
        environment=$mapped_environment
        deploy_force="1"
    fi
fi

if [[ -z $environment ]]; then
    if [[ "$GITHUB_REF" == "refs/tags"* ]]; then
      environment="production"
    else
      environment="edge"
    fi
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
set_output "image_latest" "$image_name:latest-$environment"
set_output "image_version" "$image_name:$version"
set_output "image_exists" "$image_exists"
set_output "deploy_force" "$deploy_force"
set_output "skip_lint" "$image_exists"
set_output "skip_test" "$image_exists"
set_output "skip_build" "$image_exists"
