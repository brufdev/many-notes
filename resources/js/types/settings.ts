export interface Settings {
    local_auth_enabled: boolean;
    registration: boolean;
    auto_update_check: boolean;
}

export type AdminEditableSettings = Pick<Settings, 'registration' | 'auto_update_check'>;
