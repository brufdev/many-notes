export interface AppPageProps {
    app?: {
        user?: User
        settings?: Settings
        metadata?: Metadata
    }

    [key: string]: any
}

export interface User {
    id: number
    name: string
    email: string
    role: string
}

export interface Settings {
    local_auth_enabled: boolean
    registration: boolean
    auto_update_check: boolean
}

export interface Metadata {
    app_version: string
    latest_version: string
    github_url: string
    update_available: boolean
    upload_max_filesize: string
}
