interface HighlightTextProps {
    text: string;
    highlight?: string;
    className?: string;
}

export function HighlightText({ text, highlight, className = '' }: HighlightTextProps) {
    if (!highlight || !highlight.trim()) {
        return <>{text}</>;
    }

    const parts = text.split(new RegExp(`(${highlight.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi'));
    
    return (
        <>
            {parts.map((part, index) => 
                part.toLowerCase() === highlight.toLowerCase() ? (
                    <mark key={index} className="bg-yellow-300 dark:bg-yellow-500/70 text-inherit rounded px-0.5">
                        {part}
                    </mark>
                ) : (
                    <span key={index}>{part}</span>
                )
            )}
        </>
    );
}
