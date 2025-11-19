/* ============================================================
   ==========  ORIGINAL IMPORT (TIDAK DIUBAH)  =================
=============================================================== */

import * as React from "react";
import { useState } from "react";

import { NavUser } from "@/components/nav-user";
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from "@/components/ui/sidebar";

import { ChevronDown, ChevronRight } from "lucide-react";
import { cn } from "@/lib/utils";

/* ============================================================
   ==========  ORIGINAL INTERFACE (TIDAK DIUBAH)  ==============
=============================================================== */

interface AppSidebarProps extends React.ComponentProps<typeof Sidebar> {
    active?: string;
    user: {
        name: string;
        username: string;
        photo: string;
    };
    appName: string;
    navData: {
        title: string;
        groupIcon?: React.ElementType;
        collapsible?: boolean;
        items: {
            title: string;
            url: string;
            icon: React.ElementType;
        }[];
    }[];
}

/* ============================================================
   =============  COMPONENT UTAMA  ==============================
=============================================================== */

export function AppSidebar({
    active = "",
    user,
    appName,
    navData,
    ...props
}: AppSidebarProps) {
    return (
        <Sidebar collapsible="offcanvas" {...props}>
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton asChild>
                            <a href="#">
                                <img
                                    src="/img/logo/sdi-logo-dark.png"
                                    className="w-6 block dark:hidden"
                                />
                                <img
                                    src="/img/logo/sdi-logo-light.png"
                                    className="w-6 hidden dark:block"
                                />
                                <span>{appName}</span>
                            </a>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <SidebarGroup>
                    {/* LOOP MENU */}
                    {navData.map(
                        (group: {
                            title: string;
                            groupIcon?: React.ElementType;
                            collapsible?: boolean;
                            items: {
                                title: string;
                                url: string;
                                icon: React.ElementType;
                            }[];
                        }) => (
                            <div className="mb-2" key={group.title}>
                                {/* LABEL hanya untuk non-collapsible */}
                                {!group.collapsible && (
                                    <SidebarGroupLabel>
                                        {group.groupIcon && (
                                            <group.groupIcon className="w-4 h-4 mr-2 text-muted-foreground" />
                                        )}
                                        {group.title}
                                    </SidebarGroupLabel>
                                )}

                                {/* ==== COLLAPSIBLE MENU ==== */}
                                {group.collapsible ? (
                                    <LppmCollapsibleMenu
                                        group={group}
                                        active={active}
                                    />
                                ) : (
                                    <SidebarMenu>
                                        {group.items.map((item) => (
                                            <SidebarMenuItem key={item.title}>
                                                <SidebarMenuButton
                                                    asChild
                                                    className={menuActive(
                                                        item.title,
                                                        active
                                                    )}
                                                >
                                                    <a href={item.url}>
                                                        <item.icon />
                                                        <span>
                                                            {item.title}
                                                        </span>
                                                    </a>
                                                </SidebarMenuButton>
                                            </SidebarMenuItem>
                                        ))}
                                    </SidebarMenu>
                                )}
                            </div>
                        )
                    )}
                </SidebarGroup>
            </SidebarContent>

            <SidebarFooter>
                <NavUser user={user} />
            </SidebarFooter>
        </Sidebar>
    );
}

/* ============================================================
   ===========  COMPONENT COLLAPSIBLE MENU  ====================
=============================================================== */

function LppmCollapsibleMenu({
    group,
    active,
}: {
    group: {
        title: string;
        groupIcon?: React.ElementType;
        items: {
            title: string;
            url: string;
            icon: React.ElementType;
        }[];
    };
    active: string;
}) {
    const [open, setOpen] = useState(false);

    return (
        <>
            {/* BUTTON UTAMA (TANPA LABEL DI ATAS) */}
            <button
                onClick={() => setOpen(!open)}
                className="flex items-center justify-between w-full px-2 py-2 hover:bg-accent rounded-md transition-colors"
            >
                <div className="flex items-center gap-2">
                    {group.groupIcon && <group.groupIcon className="w-4 h-4" />}
                    <span className="text-sm">{group.title}</span>
                </div>

                {open ? (
                    <ChevronDown className="w-4 h-4 transition-transform" />
                ) : (
                    <ChevronRight className="w-4 h-4 transition-transform" />
                )}
            </button>

            {open && (
                <SidebarMenu className="ml-4 mt-1 border-l border-accent pl-3 space-y-1 animate-slideDown">
                    {group.items.map((item) => (
                        <SidebarMenuItem key={item.title}>
                            <SidebarMenuButton
                                asChild
                                className={menuActive(item.title, active)}
                            >
                                <a href={item.url}>
                                    <item.icon />
                                    <span>{item.title}</span>
                                </a>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    ))}
                </SidebarMenu>
            )}
        </>
    );
}

/* ============================================================
   ===========  HELPER ACTIVE MENU  ============================
=============================================================== */

function menuActive(title: string, active: string) {
    return cn("hover:bg-primary/5 hover:text-primary", {
        "bg-primary/5 text-primary border-l border-primary":
            active.startsWith(title),
    });
}
