import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
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
import { Cars2 } from "./api-client";
import {
  formatRoubles,
  getFuelTypeDisplayName,
  getTransmissionDisplayName,
} from "@/lib/utils";
import {
  Dialog,
  DialogTrigger,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
  DialogClose,
} from "@/components/ui/dialog";
import { ModalCard } from "./ModalCard";

export const Card = ({ car }: { car: Cars2 }) => {
  const currentSchema = car.rent_term?.schemas![0]!;

  return (
    <div className="w-80 mx-auto text-gray-700 bg-white shadow-md rounded-xl mb-8 pb-4">
      <div>
        <div className="absolute z-50 text-gray p-2 bg-yellow rounded-tl-lg rounded-br-lg font-medium shadow">
          {car.park_name}
        </div>
        <img
          className="object-cover w-full h-52 rounded-t-xl"
          src={car.images![0]!}
        />
      </div>
      <div className="px-4">
        <h1 className="text-center my-4">{`${car.brand} ${car.model} ${car.year_produced}`}</h1>
        <div className="flex flex-col items-center  mx-auto space-y-2">
          <Badge
            className="font-semibold text-lg mb-2"
            variant="outline"
          >{`${formatRoubles(currentSchema.daily_amount!)} ${
            currentSchema.working_days
          } раб./${currentSchema.non_working_days} вых`}</Badge>
          <Badge variant="outline">
            Депозит {formatRoubles(car.rent_term?.deposit_amount_total!)} (
            {formatRoubles(car.rent_term?.deposit_amount_daily!)}
            /день)
          </Badge>
          <Badge variant="outline">Комиссия {car.commission}</Badge>
          <div className="flex justify-around w-full">
            <Badge variant="outline">
              {getFuelTypeDisplayName(car.fuel_type)}
            </Badge>
            <Badge variant="outline">
              {getTransmissionDisplayName(car.transmission_type)}
            </Badge>
          </div>

          {!!car.self_employed && (
            <Badge variant="outline">Для самозанятых</Badge>
          )}
          {!!car.rent_term?.is_buyout_possible && (
            <Badge variant="outline">Выкуп автомобиля</Badge>
          )}
        </div>
        <div className="text-center mt-4">
          <Dialog>
            <DialogTrigger asChild>
              <Button size={"lg"}>Подробнее</Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-[800px]">
              <DialogHeader>
                <DialogTitle></DialogTitle>
                {/* <DialogDescription>DialogDescription</DialogDescription> */}
              </DialogHeader>
              <ModalCard car={car} />
              {/* <DialogFooter>
                <DialogClose asChild>
                  <Button>Выбрать</Button>
                </DialogClose>
              </DialogFooter> */}
            </DialogContent>
          </Dialog>
        </div>
      </div>
    </div>
  );
};
