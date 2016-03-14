#!/bin/sh

# where's the source files?
SRC='.'

# for what branch to trigger
BRANCH='master'

# github repo
REPO='cosmocode/dokuwiki-plugin-struct'

# ---- About -------------------------------------------------------
#
# This script uses apigen to generate the documentation for the
# repository configured above. When run locally, the documentation
# will be placed in the 'docs' folder.
# However this script can also be run from travis. This requires
# the setup of a secret token as described at http://bit.ly/1MNbPn0
#
# Additional configuration can be done within an apigen.neon file
#
# ---- no modifications below ---------------------------------------

# when on travis, build outside of repository, otherwise locally
if [ -z "$TRAVIS" ]; then
    DST='docs'
else
    DST='../gh-pages'
    if [ "$TRAVIS_PHP_VERSION"  != '5.6'     ]; then exit; fi
    if [ "$TRAVIS_BRANCH"       != "$BRANCH" ]; then exit; fi
    if [ "$TRAVIS_PULL_REQUEST" != 'false'   ]; then exit; fi
    if [ -z "$GH_TOKEN"                      ]; then
        echo "GH_TOKEN not set! See: http://bit.ly/1MNbPn0"
        exit
    fi
fi

# Get ApiGen.phar
wget http://www.apigen.org/apigen.phar -O apigen.phar

# Generate SDK Docs
php apigen.phar generate --template-theme="bootstrap" -s $SRC -d $DST


### if we're not on travis, we're done
if [ -z "$TRAVIS" ]; then exit; fi

# go to the generated docs
cd $DST || exit

# Set identity
git config --global user.email "travis@travis-ci.org"
git config --global user.name "Travis"

# Add branch
git init
git remote add origin https://${GH_TOKEN}@github.com/${REPO}.git > /dev/null
git checkout -B gh-pages

# Push generated files
git add .
git commit -m "Docs updated by Travis"
git push origin gh-pages -fq > /dev/null
