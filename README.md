# Stratos ERP

This repository contains the modules that are used by the stratoserp install
profile.

In general, you wont want to use these directly.

You can test the system out with docker by pasting these commands.

mkdir -p /tmp/shared && \
chmod 777 /tmp/shared && \
docker run --rm -p 80:8080 -e SQLITE_DATABASE=/shared/database \
--mount type=bind,source=/tmp/shared,target=/shared singularo/stratoserp
