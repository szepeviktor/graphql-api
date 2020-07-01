#!/bin/bash
# This bash script generates the JS build,
# by running `npm run build` on all scripts (blocks/editor-scripts/packages)

# Current dir
DIR="$( dirname ${BASH_SOURCE[0]} )"

# Function `buildScripts` will run `npm run build`
# on all folders in the current directory
buildScripts(){
    CURRENT_DIR=$( pwd )
    echo "In folder '$CURRENT_DIR'"
    for file in ./*
    do
        # Make sure it is a directory
        if [ -d "$file" ]; then
            echo "In subfolder '$file'"
            cd "$file"
            npm run build
            cd ..
        fi
    done
}

# First create the symlinks to node_modules/ everywhere
bash -x "$DIR/create-node-modules-symlinks.sh" >/dev/null 2>&1

# Blocks
cd "$DIR/../../blocks/"
buildScripts

# Editor Scripts
cd "$DIR/../../editor-scripts/"
buildScripts

# Packages
cd "$DIR/../../packages/"
buildScripts