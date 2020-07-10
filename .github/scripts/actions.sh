#!/usr/bin/env bash
function set_output() {
  echo "::set-output name=$1::$2"
}

function set_env() {
  echo "::set-env name=$1::$2"
}
