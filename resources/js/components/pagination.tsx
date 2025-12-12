import { Link } from "@inertiajs/react";
import { Pagination } from "@/types/pagination";
import {
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
} from "lucide-react";

type PaginationLinks = Pagination<any>["links"];

interface PaginationCompProps {
  links: PaginationLinks;
  onPageChange?: (url: string, scrollTargetId?: string) => void;
  scrollTargetId?: string;
}

export default function PaginationComp({
  links,
    onPageChange,
    scrollTargetId
}: PaginationCompProps) {
  const getPaginationContent = (label: string) => {
    const cleanLabel = label.replace(/&laquo;|&raquo;|&lt;|&gt;/g, "").trim();

    if (label.includes("&laquo;") || cleanLabel.toLowerCase().includes("previous")) {
      return <ChevronsLeft className="h-4 w-4" />;
    }
    if (label.includes("&raquo;") || cleanLabel.toLowerCase().includes("next")) {
      return <ChevronsRight className="h-4 w-4" />;
    }
    return cleanLabel;
  };

  return (
    <div className="flex flex-wrap items-center gap-1 mt-4">
      {links.map((link, index) => {
        const content = getPaginationContent(link.label);
        const isIcon = typeof content !== "string";

        // Case 1: Use custom page change handler
       if (onPageChange && link.url) {
  return (
    <button
      key={index}
      onClick={() => onPageChange(link.url!, scrollTargetId)}
      disabled={!link.url}
      className={`px-3 py-2 text-sm font-medium rounded-md border transition-colors
        ${link.active
          ? "bg-primary text-primary-foreground border-primary shadow-sm"
          : "bg-background text-foreground border-border hover:bg-muted hover:text-muted-foreground"
        }
        ${!link.url ? "opacity-50 pointer-events-none cursor-default" : "cursor-pointer"}
        ${isIcon ? "min-w-10" : "min-w-10"} flex items-center justify-center`}
    >
      {content}
    </button>
  );
}
        // Case 2: Default to Inertia Link
        return (
          <Link
            key={index}
            href={link.url ?? "#"}
            preserveState={true}
            preserveScroll={true}
            className={`px-3 py-2 text-sm font-medium rounded-md border transition-colors
              ${link.active
                ? "bg-primary text-primary-foreground border-primary shadow-sm"
                : "bg-background text-foreground border-border hover:bg-muted hover:text-muted-foreground"
              }
              ${!link.url ? "opacity-50 pointer-events-none cursor-default" : "cursor-pointer"}
              ${isIcon ? "min-w-10" : "min-w-10"} flex items-center justify-center`}
          >
            {content}
          </Link>
        );
      })}
    </div>
  );
}
