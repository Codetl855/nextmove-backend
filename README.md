# Next-Move-Api

Setup
-----

**Note:** You need to clone the repository and be in the project root directory to run setup commands.

**For Windows Users:**
To set up the project on a Windows machine, you can use the setup.ps1 PowerShell script provided in the repository. Please follow these steps:

1. Install Docker Desktop from the official Docker website.
2. Make sure Docker is running on your system before proceeding.
3. Open PowerShell and run `.\setup.ps1` or you can also pass an argument e:g `.\setup.ps1 build`.

**For Linux/macOS Users:**
You need to ensure docker engine is running on your machine.
Please follow these steps:

- Setup docker containers `./setup build`
- Setup application `./setup start`
- Base api url `http://0.0.0.0:8100`

Useful commands
---------------

- SSH in to web server `./setup ssh`
- SSH in to mysql server `./setup ssh mysql`
- Stop docker containers `./setup stop`
- Delete docker containers `./setup delete`
- Run automated tests `./setup test`
- Open mailhog in your browser `http://0.0.0.0:8035`

**Note:** To use database you can either ssh in to mysql server or use any mysql supported client. The connection details are available in `.env.example` file and use port `3310` if you're using a mysql supported client.