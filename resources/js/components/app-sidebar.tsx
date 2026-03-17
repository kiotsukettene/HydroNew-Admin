import { NavMain } from '@/components/nav-main';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useNavigationLock } from '@/hooks/use-navigation-lock';
import { resolveUrl } from '@/lib/utils';
import { dashboard } from '@/routes';
import users from '@/routes/users';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Airplay, ChartBar,  LayoutGrid,  MessageCircleMore,  Users } from 'lucide-react';
import AppLogo from './app-logo';
import analytics from '@/routes/analytics';
import devices from '@/routes/devices';
import feedback from '@/routes/feedback';

export function AppSidebar() {
    const page = usePage<SharedData>();
    const { unrepliedFeedbackCount } = page.props;
    const { isNavigating } = useNavigationLock();

    const handleClick = (e: React.MouseEvent, href: string) => {
        const resolved = resolveUrl(href);
        if (page.url.startsWith(resolved) || isNavigating()) {
            e.preventDefault();
        }
    };

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard().url,
            icon: LayoutGrid,
        },
        {
            title: 'Users',
            href: users.index().url,
            icon: Users,
        },
        {
            title: 'Devices',
            href: devices.index().url,
            icon: Airplay,
        },
        {
            title: 'Analytics',
            href: analytics.index().url,
            icon: ChartBar,
        },
        {
            title: 'Feedback',
            href: feedback.index().url,
            icon: MessageCircleMore,
            badge: unrepliedFeedbackCount,
        }
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link
                                href={dashboard().url}
                                prefetch
                                onClick={(e) => handleClick(e, dashboard().url)}
                            >
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                {/* <NavFooter items={footerNavItems} className="mt-auto" /> */}
                {/* <NavUser /> */}


            </SidebarFooter>
        </Sidebar>
    );
}
