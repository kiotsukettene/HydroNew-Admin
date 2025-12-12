import React from 'react'
import { Search, Loader2 } from 'lucide-react'
import { Input } from '@/components/ui/input'

interface SearchInputProps {
  placeholder?: string;
  value?: string;
  onChange?: (value: string) => void;
  isLoading?: boolean;
}

export default function SearchInput({
  placeholder = "Search...",
  value,
  onChange,
  isLoading = false
}: SearchInputProps) {
  return (
    <div className="relative w-full max-w-sm">
      {isLoading ? (
        <Loader2 className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-muted-foreground" />
      ) : (
        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
      )}
      <Input
        type="text"
        placeholder={placeholder}
        value={value}
        onChange={(e) => onChange?.(e.target.value)}
        className="pl-9"
      />
    </div>
  )
}



