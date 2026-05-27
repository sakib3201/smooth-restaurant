import * as React from "react"

import { cn } from "@/admin/lib/utils"

function Card({
  className,
  size = "default",
  ...props
}: React.ComponentProps<"div"> & { size?: "default" | "sm" }) {
  return (
    <div
      data-slot="card"
      data-size={size}
      className={cn(
        "group/card sr-flex sr-flex-col sr-gap-4 sr-overflow-hidden sr-rounded-xl sr-bg-card sr-py-4 sr-text-sm sr-text-card-foreground sr-ring-1 sr-ring-foreground/10 has-data-[slot=card-footer]:sr-pb-0 has-[>img:first-child]:sr-pt-0 data-[size=sm]:sr-gap-3 data-[size=sm]:sr-py-3 data-[size=sm]:has-data-[slot=card-footer]:sr-pb-0 *:[img:first-child]:sr-rounded-t-xl *:[img:last-child]:sr-rounded-b-xl",
        className
      )}
      {...props}
    />
  )
}

function CardHeader({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="card-header"
      className={cn(
        "group/card-header @container/card-header sr-grid sr-auto-rows-min sr-items-start sr-gap-1 sr-rounded-t-xl sr-px-4 group-data-[size=sm]/card:sr-px-3 has-data-[slot=card-action]:sr-grid-cols-[1fr_auto] has-data-[slot=card-description]:sr-grid-rows-[auto_auto] [.border-b]:sr-pb-4 group-data-[size=sm]/card:[.border-b]:sr-pb-3",
        className
      )}
      {...props}
    />
  )
}

function CardTitle({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="card-title"
      className={cn(
        "sr-font-heading sr-text-base sr-leading-snug sr-font-medium group-data-[size=sm]/card:sr-text-sm",
        className
      )}
      {...props}
    />
  )
}

function CardDescription({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="card-description"
      className={cn("sr-text-sm sr-text-muted-foreground", className)}
      {...props}
    />
  )
}

function CardAction({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="card-action"
      className={cn(
        "sr-col-start-2 sr-row-span-2 sr-row-start-1 sr-self-start sr-justify-self-end",
        className
      )}
      {...props}
    />
  )
}

function CardContent({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="card-content"
      className={cn("sr-px-4 group-data-[size=sm]/card:sr-px-3", className)}
      {...props}
    />
  )
}

function CardFooter({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="card-footer"
      className={cn(
        "sr-flex sr-items-center sr-rounded-b-xl sr-border-t sr-bg-muted/50 sr-p-4 group-data-[size=sm]/card:sr-p-3",
        className
      )}
      {...props}
    />
  )
}

export {
  Card,
  CardHeader,
  CardFooter,
  CardTitle,
  CardAction,
  CardDescription,
  CardContent,
}
