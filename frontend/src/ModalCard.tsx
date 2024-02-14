import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "@/components/ui/collapsible";
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
import { Separator } from "@/components/ui/separator";
import { useState } from "react";
import { formatRoubles, getFuelTypeDisplayName, getTransmissionDisplayName } from "@/lib/utils";

export const ModalCard = ({ car }: { car: Cars2 }) => {
  const [phoneRequested, setPhoneRequested] = useState(false);

  const currentSchema = car.rent_term?.schemas![0]!;

  return (
    <>
      <div className="flex flex-col justify-center overflow-y-auto">
        <img
          className="object-cover w-full h-24 rounded-t-xl"
          src={car.images![0]}
        />
        <div className="space-y-2">
          <h1 className="text-center my-4">{`${car.brand} ${car.model} ${car.year_produced}`}</h1>
          <p className="text-base font-semibold text-gray">
            Парк: {car.park_name}
          </p>
          <Separator />
          <p className="text-base font-semibold text-gray">
            Адрес: {car.division?.address}
          </p>
          <Separator />
          <p className="text-base font-semibold text-gray">
            Минимальный срок аренды: {car.rent_term?.minimum_period_days}
          </p>
          <div className="text-center">
            {!phoneRequested && (
              <Button
                size={"lg"}
                onClick={() => setPhoneRequested(true)}
                variant="secondary"
              >
                Показать номер
              </Button>
            )}
            {phoneRequested && (
              <Button size={"lg"} variant="secondary">
                <a href={"tel:" + car.phone}>{car.phone}</a>
              </Button>
            )}
          </div>
          <div className="text-center">
            <Button size={"lg"}>Забронировать</Button>
          </div>
          <div className="text-sm text-gray-700 mt-4"></div>
          <Collapsible>
            <CollapsibleTrigger>
              {car.about?.substring(0, 50)} ▼
            </CollapsibleTrigger>
            <CollapsibleContent>
              <div className="text-sm text-gray-700">
                {car.about?.substring(50)}
              </div>
            </CollapsibleContent>
          </Collapsible>
        </div>

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

          {car.self_employed && (
            <Badge variant="outline">Для самозанятых</Badge>
          )}
          {!!car.rent_term?.is_buyout_possible && (
            <Badge variant="outline">Выкуп автомобиля</Badge>
          )}
        </div>
      </div>
    </>
  );
};
