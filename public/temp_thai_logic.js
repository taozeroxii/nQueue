function getThaiNumberFiles(num) {
    // Files provided by user/implied: 0-9, 10, 100, 1000.
    // Possibly 11 (Prompt4_11.wav) based on directory list.

    const files = [];

    if (num > 1000) {
        // Not supported well, just read digits? Or clamp?
        // For now, let's treat > 1000 as separate digits or just 1000? 
        // Let's assume queue <= 1000.
    }

    if (num === 0) return ['Prompt4/Prompt4_0.wav'];
    if (num === 1000) return ['Prompt4/Prompt4_1000.wav'];

    // Hundreds
    const hundreds = Math.floor(num / 100);
    const remainder100 = num % 100;

    if (hundreds > 0) {
        files.push(`Prompt4/Prompt4_${hundreds}.wav`);
        files.push('Prompt4/Prompt4_100.wav');
    }

    // Tens & Ones
    if (remainder100 === 0 && hundreds > 0) {
        return files;
    }

    // Special check: do we have Prompt4_11? yes. 
    // If num is exactly 11 (or ends in 11?), we could use it?
    // But logic 55 -> 5 (Ha), 10 (Sip), 5 (Ha).
    // Logic 21 -> 2 (Song), 10 (Sip), 1 (Et/Nueng).
    // If we lack "Et", "Yi", we stick to basic digits + 10.

    // Tens
    const tens = Math.floor(remainder100 / 10);
    const ones = remainder100 % 10;

    if (tens > 0) {
        if (tens === 1) {
            // 10-19.
            // Just 10? Or does 11 have special file?
            // User requested 55 -> 5, 10, 5. NOT 50, 5.
            // So 15 -> 10, 5.
            // 11 -> 11 (if special) or 10, 1.
            // Let's use Prompt4_11 if exact match for 11? 
            // But if 111? -> 100, 11?
            // Let's stick to standard decomposition:
            // 10 -> Prompt4_10.wav
            files.push('Prompt4/Prompt4_10.wav');
        } else {
            // 20-90 -> digit, 10.
            files.push(`Prompt4/Prompt4_${tens}.wav`);
            files.push('Prompt4/Prompt4_10.wav');
        }
    }

    // Ones
    if (ones > 0) {
        // if tens > 0 and ones == 1, usually "Et". 
        // Since we don't have Et file, Prompt4_1. (Nueng)
        // Unless Prompt4_11 IS "Sip Et"?
        // Let's just use Prompt4_{ones}.

        // Special case: if remainder100 == 11, and we have Prompt4_11.wav
        // We might want to use it instead of 10, 1?
        // But files.push already handled 10.
        // Let's keep it simple as requested: 55 -> 5, 10, 5.
        files.push(`Prompt4/Prompt4_${ones}.wav`);
    }

    return files;
}
