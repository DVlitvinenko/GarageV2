import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import econom from "./assets/car_icons/econom.png";
import comfort from "./assets/car_icons/comfort.png";
import comfortPlus from "./assets/car_icons/comfort-plus.png";
import business from "./assets/car_icons/business.png";
import { Checkbox } from "@/components/ui/checkbox";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

export const Card = () => (
  <>
    <div className="w-80 mx-auto">
      <div className="text-center">
        <Button>Забронировать</Button>
      </div>
      <div className="text-center">
        <Button>показать номер</Button>
      </div>
    </div>
  </>
);
