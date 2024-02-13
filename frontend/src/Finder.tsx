import { Button } from "@/components/ui/button";
import econom from "./assets/car_icons/econom.png";
import comfort from "./assets/car_icons/comfort.png";
import comfortPlus from "./assets/car_icons/comfort-plus.png";
import business from "./assets/car_icons/business.png";
import { Checkbox } from "@/components/ui/checkbox";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { useState } from "react";
import { CarClass, FuelType } from "./api-client";
import { Slider } from "@/components/ui/slider";
import { Label } from "@/components/ui/label";

const DEFAULT_COMMISSION_PERCENTAGE = 4;

export const CarFinder = () => {
  const [filters, setFilters] = useState<any>({
    carClass: CarClass.Economy,
    commission: DEFAULT_COMMISSION_PERCENTAGE,
    fuelType: FuelType.Gas,
  });

  return (
    <>
      <div className="">
        <div className="flex my-8 justify-evenly">
          {[
            [CarClass.Economy, econom, "Эконом"],
            [CarClass.Comfort, comfort, "Комфорт"],
            [CarClass.ComfortPlus, comfortPlus, "Комфорт+"],
            [CarClass.Business, business, "Бизнес"],
          ].map((x) => {
            const [carClass, img, title] = x;

            return (
              <div key={carClass} className="flex flex-col items-center">
                <img
                  onClick={() => setFilters({ ...filters, carClass })}
                  key={carClass}
                  className={`rounded mx-auto h-16 w-16 ${
                    x[0] === filters.carClass
                      ? "shadow border-2 border-yellow"
                      : ""
                  }`}
                  src={img}
                />
                <span className="text-xs font-bold text-gray">{title}</span>
              </div>
            );
          })}
        </div>
        <div className="flex justify-between my-4">
          <Checkbox title="Для самозанятых" />
          <Checkbox title="Выкуп автомобиля" />
        </div>
        {/* <DropdownMenu>
          <DropdownMenuTrigger>Сначала дешевые</DropdownMenuTrigger>
          <DropdownMenuContent>
            <DropdownMenuItem>Сначала дорогие</DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu> */}
        <div className="flex justify-between my-4">
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="outline">Марка авто</Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent>
              <DropdownMenuItem>BMW</DropdownMenuItem>
              <DropdownMenuItem>Lada</DropdownMenuItem>
              <DropdownMenuItem>Chery</DropdownMenuItem>
              <DropdownMenuItem>Geely</DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="outline">
                {filters.fuelType || "Любой тип топлива"}
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent>
              {[
                [FuelType.Gasoline, "Бензин"],
                [FuelType.Gas, "Газ"],
                [undefined, "Любой тип топлива"],
              ].map((x) => {
                const [fuelType, title] = x;

                return (
                  <DropdownMenuItem
                    onClick={() =>
                      setFilters({ ...filters, fuelType: fuelType })
                    }
                  >
                    {title}
                  </DropdownMenuItem>
                );
              })}
            </DropdownMenuContent>
          </DropdownMenu>
        </div>

        <div className="my-4 space-y-2">
          <Label>Комиссия парка не выше {filters.commission}%</Label>
          <Slider
            onValueChange={(e) => setFilters({ ...filters, commission: e[0] })}
            defaultValue={[DEFAULT_COMMISSION_PERCENTAGE]}
            max={10}
            step={0.5}
          />
        </div>
      </div>
    </>
  );
};