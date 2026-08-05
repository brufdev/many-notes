const EMAIL_PATTERN = /^[^>\s]+@[^>\s]+\.[^>\s]+$/;
const URL_PATTERN = /^https?:\/\//i;

export function isValidEmail(value: string): boolean {
    return EMAIL_PATTERN.test(value);
}

export function isValidUrl(value: string): boolean {
    return URL_PATTERN.test(value);
}
