import { Tabs as TabsPrimitive } from "@base-ui/react/tabs"
import { cva, type VariantProps } from "class-variance-authority"

import { cn } from "@/admin/lib/utils"

function Tabs({
  className,
  orientation = "horizontal",
  ...props
}: TabsPrimitive.Root.Props) {
  return (
    <TabsPrimitive.Root
      data-slot="tabs"
      data-orientation={orientation}
      className={cn(
        "group/tabs sr-flex sr-gap-2 data-horizontal:sr-flex-col",
        className
      )}
      {...props}
    />
  )
}

const tabsListVariants = cva(
  "group/tabs-list sr-inline-flex sr-w-fit sr-items-center sr-justify-center sr-rounded-lg sr-p-[3px] sr-text-muted-foreground group-data-horizontal/tabs:sr-h-8 group-data-vertical/tabs:sr-h-fit group-data-vertical/tabs:sr-flex-col data-[variant=line]:sr-rounded-none",
  {
    variants: {
      variant: {
        default: "sr-bg-muted",
        line: "sr-gap-1 sr-bg-transparent",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
)

function TabsList({
  className,
  variant = "default",
  ...props
}: TabsPrimitive.List.Props & VariantProps<typeof tabsListVariants>) {
  return (
    <TabsPrimitive.List
      data-slot="tabs-list"
      data-variant={variant}
      className={cn(tabsListVariants({ variant }), className)}
      {...props}
    />
  )
}

function TabsTrigger({ className, ...props }: TabsPrimitive.Tab.Props) {
  return (
    <TabsPrimitive.Tab
      data-slot="tabs-trigger"
      className={cn(
        "sr-relative sr-inline-flex sr-h-[calc(100%-1px)] sr-flex-1 sr-items-center sr-justify-center sr-gap-1.5 sr-rounded-md sr-border sr-border-transparent sr-px-1.5 sr-py-0.5 sr-text-sm sr-font-medium sr-whitespace-nowrap sr-text-foreground/60 sr-transition-all group-data-vertical/tabs:sr-w-full group-data-vertical/tabs:sr-justify-start hover:sr-text-foreground focus-visible:sr-border-ring focus-visible:sr-ring-[3px] focus-visible:sr-ring-ring/50 focus-visible:sr-outline-1 focus-visible:sr-outline-ring disabled:sr-pointer-events-none disabled:sr-opacity-50 has-data-[icon=inline-end]:sr-pr-1 has-data-[icon=inline-start]:sr-pl-1 aria-disabled:sr-pointer-events-none aria-disabled:sr-opacity-50 dark:sr-text-muted-foreground dark:hover:sr-text-foreground group-data-[variant=default]/tabs-list:data-active:sr-shadow-sm group-data-[variant=line]/tabs-list:data-active:sr-shadow-none [&_svg]:sr-pointer-events-none [&_svg]:sr-shrink-0 [&_svg:not([class*='size-'])]:sr-size-4",
        "group-data-[variant=line]/tabs-list:sr-bg-transparent group-data-[variant=line]/tabs-list:data-active:sr-bg-transparent dark:group-data-[variant=line]/tabs-list:data-active:sr-border-transparent dark:group-data-[variant=line]/tabs-list:data-active:sr-bg-transparent",
        "data-active:sr-bg-background data-active:sr-text-foreground dark:data-active:sr-border-input dark:data-active:sr-bg-input/30 dark:data-active:sr-text-foreground",
        "after:sr-absolute after:sr-bg-foreground after:sr-opacity-0 after:sr-transition-opacity group-data-horizontal/tabs:after:sr-inset-x-0 group-data-horizontal/tabs:after:sr-bottom-[-5px] group-data-horizontal/tabs:after:sr-h-0.5 group-data-vertical/tabs:after:sr-inset-y-0 group-data-vertical/tabs:after:-sr-right-1 group-data-vertical/tabs:after:sr-w-0.5 group-data-[variant=line]/tabs-list:data-active:after:sr-opacity-100",
        className
      )}
      {...props}
    />
  )
}

function TabsContent({ className, ...props }: TabsPrimitive.Panel.Props) {
  return (
    <TabsPrimitive.Panel
      data-slot="tabs-content"
      className={cn("sr-flex-1 sr-text-sm sr-outline-none", className)}
      {...props}
    />
  )
}

export { Tabs, TabsList, TabsTrigger, TabsContent, tabsListVariants }
