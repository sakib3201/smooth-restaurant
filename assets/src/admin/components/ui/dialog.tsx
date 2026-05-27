import * as React from "react"
import { Dialog as DialogPrimitive } from "@base-ui/react/dialog"

import { cn } from "@/admin/lib/utils"
import { Button } from "@/admin/components/ui/button"
import { XIcon } from "lucide-react"

function Dialog({ ...props }: DialogPrimitive.Root.Props) {
  return <DialogPrimitive.Root data-slot="dialog" {...props} />
}

function DialogTrigger({ ...props }: DialogPrimitive.Trigger.Props) {
  return <DialogPrimitive.Trigger data-slot="dialog-trigger" {...props} />
}

function DialogPortal({ ...props }: DialogPrimitive.Portal.Props) {
  return <DialogPrimitive.Portal data-slot="dialog-portal" {...props} />
}

function DialogClose({ ...props }: DialogPrimitive.Close.Props) {
  return <DialogPrimitive.Close data-slot="dialog-close" {...props} />
}

function DialogOverlay({
  className,
  ...props
}: DialogPrimitive.Backdrop.Props) {
  return (
    <DialogPrimitive.Backdrop
      data-slot="dialog-overlay"
      className={cn(
        "sr-fixed sr-inset-0 sr-isolate sr-z-50 sr-bg-black/10 sr-duration-100 supports-backdrop-filter:sr-backdrop-blur-xs data-open:sr-animate-in data-open:sr-fade-in-0 data-closed:sr-animate-out data-closed:sr-fade-out-0",
        className
      )}
      {...props}
    />
  )
}

function DialogContent({
  className,
  children,
  showCloseButton = true,
  ...props
}: DialogPrimitive.Popup.Props & {
  showCloseButton?: boolean
}) {
  return (
    <DialogPortal>
      <DialogOverlay />
      <DialogPrimitive.Popup
        data-slot="dialog-content"
        className={cn(
          "sr-fixed sr-top-1/2 sr-left-1/2 sr-z-50 sr-grid sr-w-full sr-max-w-[calc(100%-2rem)] -sr-translate-x-1/2 -sr-translate-y-1/2 sr-gap-4 sr-rounded-xl sr-bg-popover sr-p-4 sr-text-sm sr-text-popover-foreground sr-ring-1 sr-ring-foreground/10 sr-duration-100 sr-outline-none sm:sr-max-w-sm data-open:sr-animate-in data-open:sr-fade-in-0 data-open:sr-zoom-in-95 data-closed:sr-animate-out data-closed:sr-fade-out-0 data-closed:sr-zoom-out-95",
          className
        )}
        {...props}
      >
        {children}
        {showCloseButton && (
          <DialogPrimitive.Close
            data-slot="dialog-close"
            render={
              <Button
                variant="ghost"
                className="sr-absolute sr-top-2 sr-right-2"
                size="icon-sm"
              />
            }
          >
            <XIcon
            />
            <span className="sr-sr-only">Close</span>
          </DialogPrimitive.Close>
        )}
      </DialogPrimitive.Popup>
    </DialogPortal>
  )
}

function DialogHeader({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="dialog-header"
      className={cn("sr-flex sr-flex-col sr-gap-2", className)}
      {...props}
    />
  )
}

function DialogFooter({
  className,
  showCloseButton = false,
  children,
  ...props
}: React.ComponentProps<"div"> & {
  showCloseButton?: boolean
}) {
  return (
    <div
      data-slot="dialog-footer"
      className={cn(
        "-sr-mx-4 -sr-mb-4 sr-flex sr-flex-col-reverse sr-gap-2 sr-rounded-b-xl sr-border-t sr-bg-muted/50 sr-p-4 sm:sr-flex-row sm:sr-justify-end",
        className
      )}
      {...props}
    >
      {children}
      {showCloseButton && (
        <DialogPrimitive.Close render={<Button variant="outline" />}>
          Close
        </DialogPrimitive.Close>
      )}
    </div>
  )
}

function DialogTitle({ className, ...props }: DialogPrimitive.Title.Props) {
  return (
    <DialogPrimitive.Title
      data-slot="dialog-title"
      className={cn(
        "sr-font-heading sr-text-base sr-leading-none sr-font-medium",
        className
      )}
      {...props}
    />
  )
}

function DialogDescription({
  className,
  ...props
}: DialogPrimitive.Description.Props) {
  return (
    <DialogPrimitive.Description
      data-slot="dialog-description"
      className={cn(
        "sr-text-sm sr-text-muted-foreground *:[a]:sr-underline *:[a]:sr-underline-offset-3 *:[a:hover]:sr-text-foreground",
        className
      )}
      {...props}
    />
  )
}

export {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogOverlay,
  DialogPortal,
  DialogTitle,
  DialogTrigger,
}
