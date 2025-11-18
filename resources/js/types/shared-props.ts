export interface SharedUser {
    name: string
    email: string
}

export interface AppSettings {
    local_auth_enabled: boolean
}

export interface SharedProps {
    auth?: { user: SharedUser }
    app?: { settings: AppSettings }

    [key: string]: any
}
