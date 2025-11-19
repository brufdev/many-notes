export interface AppPageProps {
    auth?: { user: User }
    app?: { setting: Setting }

    [key: string]: any
}

export interface User {
    name: string
    email: string
}

export interface Setting {
    local_auth_enabled: boolean
}
