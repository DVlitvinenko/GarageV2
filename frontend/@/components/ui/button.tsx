import * as React from "react";
import { Slot } from "@radix-ui/react-slot";
import { cva, type VariantProps } from "class-variance-authority";

import { cn } from "@/lib/utils";

const buttonVariants = cva(
  "inline-flex items-center justify-center px-3 sm:px-4 whitespace-nowrap h-12 rounded-xl text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none",
  {
    variants: {
      variant: {
        default:
          "bg-yellow text-black font-semibold w-full hover:bg-slate-900/50 dark:bg-slate-50 dark:text-slate-900 dark:hover:bg-slate-50/90",
        reject:
          "bg-red text-white hover:bg-red dark:bg-red-900 dark:text-slate-50 dark:hover:bg-red-900/90",
        outline:
          "border border-slate-200 bg-white hover:bg-slate-100 hover:text-slate-900 dark:border-slate-800 dark:bg-slate-950 dark:hover:bg-slate-800 dark:hover:text-slate-50",
        secondary:
          "bg-pale text-slate-900 font-semibold hover:bg-slate-100/80 dark:bg-slate-800 dark:text-slate-50 dark:hover:bg-slate-800/80",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
);

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  asChild?: boolean;
  onAsyncClick?: () => Promise<void | (() => void)>;
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  (
    {
      className,
      variant = "default",
      type = "button",
      asChild = false,
      onAsyncClick,
      ...props
    },
    ref
  ) => {
    const [actionInProgress, setActionInProgress] = React.useState(false);

    const Comp = asChild ? Slot : "button";

    const handleAsyncOnPress = async () => {
      setActionInProgress(true);

      const unmountCallback = await onAsyncClick!();
      if (!!unmountCallback) {
        unmountCallback();
      } else {
        setActionInProgress(false);
      }
    };

    const handler = onAsyncClick
      ? async () => {
          await handleAsyncOnPress();
        }
      : props.onClick;

    return (
      <Comp
        className={cn(buttonVariants({ variant, className }))}
        ref={ref}
        {...props}
        onClick={handler}
        children={
          <span className="flex">
            {props.children}
            {actionInProgress && (
              <div className="ml-4 font-bold animate-spin">c</div>
            )}
          </span>
        }
      />
    );
  }
);
Button.displayName = "Button";

export { Button, buttonVariants };
