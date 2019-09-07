#!/bin/bash
function decrypt() {
  gcloud kms decrypt \
    --location global \
    --keyring mathrix-education-keyring \
    --key mathrix-education-api-cloudbuild \
    --ciphertext-file "${1}.enc" \
    --plaintext-file "${1}"
  echo "Decrypted: ${1}"
}

decrypt deploy/secrets/github-oauth.github.com
