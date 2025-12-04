import { Pagination } from "@/types/pagination";
import { Link } from "@inertiajs/react";

type PaginationLinks = Pagination<any>['links'];

export default function PaginationComp({ links }: {links: PaginationLinks}) {
    return (
        <div className="flex flex-wrap items-center space-x-1 mt-4">
            {links.map((link, index) => (
                <Link
                    key = {index}
                    href={link.url ?? '#'}
                    dangerouslySetInnerHTML={{__html: link.label}}
                    className={`px-3 py-1 text-sm rounded border
                        ${link.active ? 'bg-orange-600 text-white' : 'bg-white text-gray-700'}
                        ${!link.url ? 'opacity-50 pointer-events-none' : 'hover:bg-gray-100'}`}
                />
            ))}
        </div>
    )
}
