"use client"

import * as React from "react"

import { cn } from "@/admin/lib/utils"

function Label({ className, ...props }: React.ComponentProps<"label">) {
  return (
    <label
      data-slot="label"
      className={cn(
        "sr-flex sr-items-center sr-gap-2 sr-text-sm sr-leading-none sr-font-medium sr-select-none group-data-[disabled=true]:sr-pointer-events-none group-data-[disabled=true]:sr-opacity-50 peer-disabled:sr-cursor-not-allowed peer-disabled:sr-opacity-50",
        className
      )}
      {...props}
    />
  )
}

export { Label }
