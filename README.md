#
# StratosERP
#

This repository contains the modules that are used by the StratosERP install
profile.

The installation profile is the best way to get started, rather than using the
modules directly.

On linux you can test the system out with docker by pasting these commands.

```bash
temp_dir=$(mktemp -d)
mkdir ${temp_dir}/{public,private}
sudo chmod 777 -R ${temp_dir}
docker run --rm -p 80:8080 \
  -e PRIVATE_DIR=/shared/private \
  -e SQLITE_DATABASE=/shared/database \
  --name stratoserp \
  --mount type=bind,source=${temp_dir},target=/shared \
  --mount type=bind,source=${temp_dir}/public,target=/code/web/sites/default/files \
  stratoserp/stratoserp
```

Retrieve the docker image and start it. Afterwards, you can access the system using
http://localhost and performing a standard Drupal install, choosing the
StratosERP install profile.
