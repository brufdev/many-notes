import { Settings } from './settings';

export interface AppPageProps {
    app?: {
        user?: User;
        settings?: Settings;
        metadata?: Metadata;
    };

    [key: string]: any;
}

export interface User {
    id: number;
    name: string;
    email: string;
    role: string;
}

export interface Metadata {
    app_version: string;
    latest_version: string;
    github_url: string;
    update_available: boolean;
    upload_max_filesize: string;
}
