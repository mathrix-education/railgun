#!/bin/bash
function decrypt() {
  gcloud kms decrypt \
    --location global \
    --keyring mathrix-education-keyring \
    --key education-api-key \
    --ciphertext-file ${1}.enc \
    --plaintext-file ${1}
}

decrypt storage/keychain/github-oauth.github.com
decrypt storage/keychain/google-cloud.json
