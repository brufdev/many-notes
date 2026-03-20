export function formatElapsedTime(input: string | Date): string {
    const date = typeof input === 'string' ? new Date(input) : input;
    const seconds = Math.floor((Date.now() - date.getTime()) / 1000);

    if (seconds < 60) {
        return `${seconds}s`;
    }

    const minutes = Math.floor(seconds / 60);

    if (minutes < 60) {
        return `${minutes}m`;
    }

    const hours = Math.floor(minutes / 60);

    if (hours < 24) {
        return `${hours}h`;
    }

    const days = Math.floor(hours / 24);

    if (days < 7) {
        return `${days}d`;
    }

    const weeks = Math.floor(days / 7);

    if (days < 30) {
        return `${weeks}w`;
    }

    const months = Math.floor(days / 30);

    if (months < 12) {
        return months === 1 ? '1mo' : `${months}mos`;
    }

    const years = Math.floor(days / 365);

    return `${years}y`;
}
