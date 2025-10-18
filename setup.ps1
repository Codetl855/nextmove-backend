# setup.ps1

$Host.UI.RawUI.ForegroundColor = "White"
$COLOUR_RED = "Red"
$COLOUR_GREEN = "Green"
$COLOUR_BLUE = "Blue"
$COLOUR_YELLOW = "Yellow"
$COLOUR_CYAN = "Cyan"
$NO_COLOUR = "White"

$mysql_container_name = "next-move-mysql-server"
$mailhog_container_name = "next-move-mailhog-server"
$app_container_name = "next-move-api-server"
$wait_time = 15

function Write-Info {
    param($Message)
    $Host.UI.RawUI.ForegroundColor = $COLOUR_BLUE
    Write-Host "Info: " -NoNewline
    $Host.UI.RawUI.ForegroundColor = $NO_COLOUR
    Write-Host $Message
}

function Write-Success {
    param($Message)
    $Host.UI.RawUI.ForegroundColor = $COLOUR_GREEN
    Write-Host "Success: " -NoNewline
    $Host.UI.RawUI.ForegroundColor = $NO_COLOUR
    Write-Host $Message
}

function Write-Error {
    param($Message)
    $Host.UI.RawUI.ForegroundColor = $COLOUR_RED
    Write-Host "Error: " -NoNewline
    $Host.UI.RawUI.ForegroundColor = $NO_COLOUR
    Write-Host $Message
}

function Write-Warning {
    param($Message)
    $Host.UI.RawUI.ForegroundColor = $COLOUR_YELLOW
    Write-Host "Warning: " -NoNewline
    $Host.UI.RawUI.ForegroundColor = $NO_COLOUR
    Write-Host $Message
}

function Write-Header {
    param($Message)
    $Host.UI.RawUI.ForegroundColor = $COLOUR_CYAN
    Write-Host "`n" + ("=" * 60)
    Write-Host "  $Message"
    Write-Host ("=" * 60) + "`n"
    $Host.UI.RawUI.ForegroundColor = $NO_COLOUR
}

function Show-Menu {
    Clear-Host
    Write-Header "NEXT MOVE API DOCKER MANAGEMENT"

    $Host.UI.RawUI.ForegroundColor = $COLOUR_CYAN
    Write-Host "1. Build Containers" -ForegroundColor $COLOUR_CYAN
    Write-Host "2. Start Application (Install & Migrate)"
    Write-Host "3. Stop Containers"
    Write-Host "4. Delete Containers"
    Write-Host "5. Run Tests"
    Write-Host "6. SSH into Container"
    Write-Host "7. View Container Status"
    Write-Host "8. View Logs"
    Write-Host "9. Restart Application"
    Write-Host "0. Exit"
    Write-Host ""
    $Host.UI.RawUI.ForegroundColor = $NO_COLOUR

    $choice = Read-Host "Please select an option (0-9)"
    return $choice
}

function Show-ContainerMenu {
    Write-Host "`nSelect container to access:"
    Write-Host "1. App Container ($app_container_name)"
    Write-Host "2. MySQL Container ($mysql_container_name)"
    Write-Host "3. MailHog Container ($mailhog_container_name)"
    Write-Host "0. Back to main menu"
    Write-Host ""

    $choice = Read-Host "Please select an option (0-3)"
    return $choice
}

function Show-LogsMenu {
    Write-Host "`nSelect container to view logs:"
    Write-Host "1. App Container ($app_container_name)"
    Write-Host "2. MySQL Container ($mysql_container_name)"
    Write-Host "3. MailHog Container ($mailhog_container_name)"
    Write-Host "4. All containers"
    Write-Host "0. Back to main menu"
    Write-Host ""

    $choice = Read-Host "Please select an option (0-4)"
    return $choice
}

function Get-ContainerStatus {
    Write-Header "CONTAINER STATUS"

    $containers = @($mysql_container_name, $mailhog_container_name, $app_container_name)

    $containerData = @()

    foreach ($container in $containers) {
        $containerInfo = docker inspect --format='{{.Name}}|{{.State.Status}}|{{range $p, $conf := .NetworkSettings.Ports}}{{$p}} {{end}}' $container 2>$null
        if ($containerInfo) {
            $containerInfo = $containerInfo -replace '^/', ''
            $containerData += $containerInfo
        }
        else {
            $containerData += "$container|Not found|N/A"
        }
    }

    Write-Host ("{0,-25} {1,-15} {2,-30}" -f "NAMES", "STATUS", "PORTS") -ForegroundColor $COLOUR_CYAN
    Write-Host ("{0,-25} {1,-15} {2,-30}" -f "-----", "------", "-----") -ForegroundColor $COLOUR_CYAN

    foreach ($data in $containerData) {
        $parts = $data -split '\|'
        $name = $parts[0]
        $status = $parts[1]
        $ports = if ($parts[2]) { $parts[2].Trim() } else { "No ports" }

        $statusColor = if ($status -eq "Running") { $COLOUR_GREEN } else { $COLOUR_RED }

        Write-Host ("{0,-25} " -f $name) -NoNewline
        Write-Host ("{0,-15} " -f $status) -NoNewline -ForegroundColor $statusColor
        Write-Host ("{0,-30}" -f $ports)
    }

    Write-Host "`nPress any key to continue..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
}

function New-Build {
    Write-Header "BUILDING CONTAINERS"

    $appContainerRunning = docker ps -q -f "name=$app_container_name"
    if (-not $appContainerRunning) {
        Write-Info "Bringing up containers"
        docker compose --file=docker/docker-compose.yml --project-name=$app_container_name up -d
        if ($LASTEXITCODE -eq 0) {
            Write-Success "Containers running successfully"
        }
        else {
            Write-Error "Failed to start containers"
            return $false
        }
        Write-Host ""
    }
    else {
        Write-Warning "Containers are already running"
    }
    return $true
}

function Start-App {
    Write-Header "STARTING APPLICATION"

    $appContainerRunning = docker ps -q -f "name=$app_container_name"
    if (-not $appContainerRunning) {
        Write-Error "Containers are not running. Please build first."
        return $false
    }

    Write-Info "Waiting for $wait_time seconds while containers start running"
    Write-Host "Please wait..." -NoNewline
    for ($i = 1; $i -le $wait_time; $i++) {
        Write-Host "." -NoNewline
        Start-Sleep -Seconds 1
    }
    Write-Host " Done!"
    Write-Host ""

    Write-Info "Checking PHP dependencies"
    $vendorExists = docker exec $app_container_name bash -c "[ -d vendor ] && echo 'exists' || echo 'not exists'" 2>$null
    if ($vendorExists -like "*not exists*") {
        Write-Info "Installing PHP dependencies..."
        docker exec $app_container_name bash -c "composer install"
        if ($LASTEXITCODE -eq 0) {
            Write-Success "PHP dependencies installed successfully"
        }
        else {
            Write-Error "Failed to install PHP dependencies"
            return $false
        }
    }
    else {
        Write-Success "PHP dependencies already installed"
    }
    Write-Host ""

    Write-Info "Checking environment file"
    $envExists = docker exec $app_container_name bash -c "[ -f .env ] && echo 'exists' || echo 'not exists'" 2>$null
    if ($envExists -like "*not exists*") {
        Write-Info "Creating environment file..."
        docker exec $app_container_name bash -c "cp .env.example .env && php artisan key:generate"
        if ($LASTEXITCODE -eq 0) {
            Write-Success "Environment file setup successfully"
        }
        else {
            Write-Error "Failed to setup environment file"
            return $false
        }
    }
    else {
        Write-Success "Environment file already exists"
    }
    Write-Host ""

    Write-Info "Migrating database..."
    docker exec $app_container_name bash -c "php artisan migrate"
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Database migrated successfully"
    }
    else {
        Write-Error "Failed to migrate database"
        Write-Warning "Database might not be ready. Retrying in 10 seconds..."
        Start-Sleep -Seconds 10
        docker exec $app_container_name bash -c "php artisan migrate"
        if ($LASTEXITCODE -eq 0) {
            Write-Success "Database migrated successfully on retry"
        }
        else {
            Write-Error "Failed to migrate database after retry"
            return $false
        }
    }
    Write-Host ""

    Write-Success "Application started successfully!"
    return $true
}

function Stop-App {
    Write-Header "STOPPING CONTAINERS"

    $containers = @($mysql_container_name, $mailhog_container_name, $app_container_name)
    $stoppedCount = 0

    foreach ($container in $containers) {
        $containerRunning = docker ps -q -f "name=$container"
        if ($containerRunning) {
            Write-Info "Stopping $container container"
            docker stop $container
            if ($LASTEXITCODE -eq 0) {
                Write-Success "$container container stopped successfully"
                $stoppedCount++
            }
            else {
                Write-Error "Failed to stop $container container"
            }
            Write-Host ""
        }
        else {
            Write-Info "$container is already stopped"
        }
    }

    if ($stoppedCount -gt 0) {
        Write-Success "All containers stopped successfully"
    }
}

function Remove-App {
    Write-Header "REMOVING CONTAINERS"

    Stop-App

    $containers = @($mysql_container_name, $mailhog_container_name, $app_container_name)
    $removedCount = 0

    foreach ($container in $containers) {
        $containerExists = docker ps -a -q -f "name=$container"
        if ($containerExists) {
            Write-Info "Deleting $container container"
            docker rm $container
            if ($LASTEXITCODE -eq 0) {
                Write-Success "$container container deleted successfully"
                $removedCount++
            }
            else {
                Write-Error "Failed to delete $container container"
            }
            Write-Host ""
        }
        else {
            Write-Info "$container does not exist"
        }
    }

    if ($removedCount -gt 0) {
        Write-Success "All containers removed successfully"
    }
}

function Test-Application {
    Write-Header "RUNNING TESTS"

    $appContainerRunning = docker ps -q -f "name=$app_container_name"
    if (-not $appContainerRunning) {
        Write-Error "Application is not running. Please start first."
        return
    }

    Write-Info "Running PHPUnit tests..."
    docker exec $app_container_name bash -c "php artisan test"
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Tests failed"
    }
}

function Enter-Container {
    param($ContainerType)

    Write-Header "ACCESSING CONTAINER"

    $appContainerRunning = docker ps -q -f "name=$app_container_name"
    if (-not $appContainerRunning) {
        Write-Error "Containers are not running. Please start first."
        return
    }

    switch ($ContainerType) {
        "1" {
            Write-Info "Entering App Container..."
            docker exec -it $app_container_name bash
        }
        "2" {
            Write-Info "Entering MySQL Container..."
            docker exec -it $mysql_container_name bash
        }
        "3" {
            Write-Info "Entering MailHog Container..."
            docker exec -it $mailhog_container_name sh
        }
        default {
            Write-Error "Invalid container selection"
        }
    }
}

function Show-Logs {
    param($choice)

    Write-Header "VIEWING LOGS"

    switch ($choice) {
        "1" {
            Write-Info "Showing logs for App Container..."
            docker logs $app_container_name -f
        }
        "2" {
            Write-Info "Showing logs for MySQL Container..."
            docker logs $mysql_container_name -f
        }
        "3" {
            Write-Info "Showing logs for MailHog Container..."
            docker logs $mailhog_container_name -f
        }
        "4" {
            Write-Info "Showing logs for all containers..."
            docker-compose --file=docker/docker-compose.yml logs -f
        }
        default {
            Write-Error "Invalid selection"
        }
    }
}

function Restart-App {
    Write-Header "RESTARTING APPLICATION"

    Write-Info "Stopping containers..."
    Stop-App
    Start-Sleep -Seconds 2

    Write-Info "Starting containers..."
    if (New-Build) {
        Start-Sleep -Seconds 2
        if (Start-App) {
            Write-Success "Application restarted successfully!"
        }
    }
}

function Start-Interactive {
    do {
        $choice = Show-Menu

        switch ($choice) {
            "1" {
                if (New-Build) {
                    Write-Success "Build completed successfully!"
                }
                Pause
            }
            "2" {
                if (Start-App) {
                    Write-Success "Start completed successfully!"
                }
                Pause
            }
            "3" {
                Stop-App
                Pause
            }
            "4" {
                Remove-App
                Pause
            }
            "5" {
                Test-Application
                Pause
            }
            "6" {
                $containerChoice = Show-ContainerMenu
                if ($containerChoice -ne "0") {
                    Enter-Container $containerChoice
                }
            }
            "7" {
                Get-ContainerStatus
            }
            "8" {
                $logsChoice = Show-LogsMenu
                if ($logsChoice -ne "0") {
                    Show-Logs $logsChoice
                }
            }
            "9" {
                Restart-App
                Pause
            }
            "0" {
                Write-Info "Goodbye!"
                exit 0
            }
            default {
                Write-Error "Invalid option. Please try again."
                Pause
            }
        }
    } while ($true)
}

function Pause {
    Write-Host "`nPress any key to continue..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
}

if ($args.Count -gt 0) {
    switch ($args[0]) {
        "build" { New-Build }
        "start" { Start-App }
        "stop" { Stop-App }
        "delete" { Remove-App }
        "test" { Test-Application }
        "ssh" {
            $containerType = if ($args.Count -gt 1) { $args[1] } else { "" }
            Enter-Container $containerType
        }
        "status" { Get-ContainerStatus }
        "interactive" { Start-Interactive }
        default {
            Write-Error "Invalid argument. Use one of: build, start, stop, delete, test, ssh, status, interactive"
            exit 1
        }
    }
}
else {
    Start-Interactive
}

$Host.UI.RawUI.ForegroundColor = "White"