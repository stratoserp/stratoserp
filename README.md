# Stratos ERP

This repository contains the modules that are used by the stratoserp install
profile.

In general, you wont want to use these directly.

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
  singularo/stratoserp
```

After the image is pulled down and started, you can access the system using 
http://localhost and performing a standard Drupal install, choosing 
StratosERP as the install profile.
 