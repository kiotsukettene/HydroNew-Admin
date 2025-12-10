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
import { dashboard } from '@/routes';
import users from '@/routes/users';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { Airplay, ChartBar,  LayoutGrid,  MessageCircleMore,  Users } from 'lucide-react';
import AppLogo from './app-logo';
import analytics from '@/routes/analytics';
import devices from '@/routes/devices';
import feedback from '@/routes/feedback';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Users',
        href: users.index(),
        icon: Users,
    },
    {
        title: 'Devices',
        href: devices.index(),
        icon: Airplay,
    },
      {
        title: 'Analytics',
        href: analytics.index(),
        icon: ChartBar,
    },
    {
        title: 'Feedback',
        href: feedback.index(),
        icon: MessageCircleMore,
    }

];

// const footerNavItems: NavItem[] = [
//     {
//         title: 'Repository',
//         href: 'https://github.com/laravel/react-starter-kit',
//         icon: Folder,
//     },
//     {
//         title: 'Documentation',
//         href: 'https://laravel.com/docs/starter-kits#react',
//         icon: BookOpen,
//     },
// ];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
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
