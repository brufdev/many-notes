export interface AppPageProps {
    auth?: { user: User }
    app?: { settings: Settings }

    [key: string]: any
}

export interface User {
    name: string
    email: string
}

export interface Settings {
    local_auth_enabled: boolean
    registration: boolean
    auto_update_check: boolean
}
