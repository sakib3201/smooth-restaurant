"use client"

import * as React from "react"
import { Dialog as SheetPrimitive } from "@base-ui/react/dialog"

import { cn } from "@/admin/lib/utils"
import { Button } from "@/admin/components/ui/button"
import { XIcon } from "lucide-react"

function Sheet({ ...props }: SheetPrimitive.Root.Props) {
  return <SheetPrimitive.Root data-slot="sheet" {...props} />
}

function SheetTrigger({ ...props }: SheetPrimitive.Trigger.Props) {
  return <SheetPrimitive.Trigger data-slot="sheet-trigger" {...props} />
}

function SheetClose({ ...props }: SheetPrimitive.Close.Props) {
  return <SheetPrimitive.Close data-slot="sheet-close" {...props} />
}

function SheetPortal({ ...props }: SheetPrimitive.Portal.Props) {
  return <SheetPrimitive.Portal data-slot="sheet-portal" {...props} />
}

function SheetOverlay({ className, ...props }: SheetPrimitive.Backdrop.Props) {
  return (
    <SheetPrimitive.Backdrop
      data-slot="sheet-overlay"
      className={cn(
        "sr-fixed sr-inset-0 sr-z-50 sr-bg-black/10 sr-transition-opacity sr-duration-150 data-ending-style:sr-opacity-0 data-starting-style:sr-opacity-0 supports-backdrop-filter:sr-backdrop-blur-xs",
        className
      )}
      {...props}
    />
  )
}

function SheetContent({
  className,
  children,
  side = "right",
  showCloseButton = true,
  ...props
}: SheetPrimitive.Popup.Props & {
  side?: "top" | "right" | "bottom" | "left"
  showCloseButton?: boolean
}) {
  return (
    <SheetPortal>
      <SheetOverlay />
      <SheetPrimitive.Popup
        data-slot="sheet-content"
        data-side={side}
        className={cn(
          "sr-fixed sr-z-50 sr-flex sr-flex-col sr-gap-4 sr-bg-popover sr-bg-clip-padding sr-text-sm sr-text-popover-foreground sr-shadow-lg sr-transition sr-duration-200 sr-ease-in-out data-ending-style:sr-opacity-0 data-starting-style:sr-opacity-0 data-[side=bottom]:sr-inset-x-0 data-[side=bottom]:sr-bottom-0 data-[side=bottom]:sr-h-auto data-[side=bottom]:sr-border-t data-[side=bottom]:data-ending-style:sr-translate-y-[2.5rem] data-[side=bottom]:data-starting-style:sr-translate-y-[2.5rem] data-[side=left]:sr-inset-y-0 data-[side=left]:sr-left-0 data-[side=left]:sr-h-full data-[side=left]:sr-w-3/4 data-[side=left]:sr-border-r data-[side=left]:data-ending-style:sr-translate-x-[-2.5rem] data-[side=left]:data-starting-style:sr-translate-x-[-2.5rem] data-[side=right]:sr-inset-y-0 data-[side=right]:sr-right-0 data-[side=right]:sr-h-full data-[side=right]:sr-w-3/4 data-[side=right]:sr-border-l data-[side=right]:data-ending-style:sr-translate-x-[2.5rem] data-[side=right]:data-starting-style:sr-translate-x-[2.5rem] data-[side=top]:sr-inset-x-0 data-[side=top]:sr-top-0 data-[side=top]:sr-h-auto data-[side=top]:sr-border-b data-[side=top]:data-ending-style:sr-translate-y-[-2.5rem] data-[side=top]:data-starting-style:sr-translate-y-[-2.5rem] data-[side=left]:sm:sr-max-w-sm data-[side=right]:sm:sr-max-w-sm",
          className
        )}
        {...props}
      >
        {children}
        {showCloseButton && (
          <SheetPrimitive.Close
            data-slot="sheet-close"
            render={
              <Button
                variant="ghost"
                className="sr-absolute sr-top-3 sr-right-3"
                size="icon-sm"
              />
            }
          >
            <XIcon
            />
            <span className="sr-sr-only">Close</span>
          </SheetPrimitive.Close>
        )}
      </SheetPrimitive.Popup>
    </SheetPortal>
  )
}

function SheetHeader({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="sheet-header"
      className={cn("sr-flex sr-flex-col sr-gap-0.5 sr-p-4", className)}
      {...props}
    />
  )
}

function SheetFooter({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="sheet-footer"
      className={cn("sr-mt-auto sr-flex sr-flex-col sr-gap-2 sr-p-4", className)}
      {...props}
    />
  )
}

function SheetTitle({ className, ...props }: SheetPrimitive.Title.Props) {
  return (
    <SheetPrimitive.Title
      data-slot="sheet-title"
      className={cn(
        "sr-font-heading sr-text-base sr-font-medium sr-text-foreground",
        className
      )}
      {...props}
    />
  )
}

function SheetDescription({
  className,
  ...props
}: SheetPrimitive.Description.Props) {
  return (
    <SheetPrimitive.Description
      data-slot="sheet-description"
      className={cn("sr-text-sm sr-text-muted-foreground", className)}
      {...props}
    />
  )
}

export {
  Sheet,
  SheetTrigger,
  SheetClose,
  SheetContent,
  SheetHeader,
  SheetFooter,
  SheetTitle,
  SheetDescription,
}
