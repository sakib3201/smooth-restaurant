import { cn } from "@/admin/lib/utils"

function Skeleton({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="skeleton"
      className={cn("sr-animate-pulse sr-rounded-md sr-bg-muted", className)}
      {...props}
    />
  )
}

export { Skeleton }
