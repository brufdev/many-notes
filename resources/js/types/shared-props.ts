export interface SharedUser {
    name: string
    email: string
}

export interface SharedProps {
    auth?: { user: SharedUser }

    [key: string]: any
}
