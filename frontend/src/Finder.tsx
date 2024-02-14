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
import { CarClass, FuelType, TransmissionType } from "./api-client";
import { Slider } from "@/components/ui/slider";
import { Label } from "@/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Card } from "./Card";

const DEFAULT_COMMISSION_PERCENTAGE = 4;

export const Finder = () => {
  const [filters, setFilters] = useState<{
    brands: string[];
    carClass: CarClass;
    commission: number;
    fuelType: FuelType | null;
    transmissionType: TransmissionType | null;
  }>({
    carClass: CarClass.Economy,
    commission: DEFAULT_COMMISSION_PERCENTAGE,
    fuelType: null,
    brands: [],
    transmissionType: null,
  });

  return (
    <>
      <div className="">
        <div className=" w-80 flex my-8 justify-evenly">
          {[
            [CarClass.Economy, econom, "Эконом"],
            [CarClass.Comfort, comfort, "Комфорт"],
            [CarClass.ComfortPlus, comfortPlus, "Комфорт+"],
            [CarClass.Business, business, "Бизнес"],
          ].map((x) => {
            const [carClass, img, title] = x;

            return (
              <div key={carClass} className={`w-full flex flex-col items-center px-2 bg-white rounded ${
                x[0] === filters.carClass
                  ? "shadow border-2 border-yellow "
                  : ""
              }`}>
                <img
                  onClick={() =>
                    setFilters({ ...filters, carClass: carClass as CarClass })
                  }
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
        <div className="flex flex-col space-y-4 justify-between my-4">
          {/* <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="outline">Марка авто</Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent>
              <DropdownMenuItem>BMW</DropdownMenuItem>
              <DropdownMenuItem>Lada</DropdownMenuItem>
              <DropdownMenuItem>Chery</DropdownMenuItem>
              <DropdownMenuItem>Geely</DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu> */}

          <Dialog>
            <DialogTrigger asChild>
              <Button variant="outline">
                {!!filters.brands.length
                  ? // ? filters.brands.join(", ")
                    "Выбрано марок " + filters.brands.length
                  : "Все марки"}
              </Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-[800px]">
              <DialogHeader>
                <DialogTitle>Марка автомобиля</DialogTitle>
                {/* <DialogDescription>DialogDescription</DialogDescription> */}
              </DialogHeader>
              <div className="grid grid-cols-3 gap-4 py-4 h-[300px] overflow-y-scroll">
                {[
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                  "Bibika",
                ].map((x, i) => {
                  const title = x + i;
                  const isActive = filters.brands.some((b) => b === title);

                  return (
                    <span
                      className={`text-sm font-bold  ${
                        isActive ? "text-slate-700" : "text-slate-400"
                      }`}
                      key={title}
                      onClick={() =>
                        setFilters({
                          ...filters,
                          brands: isActive
                            ? filters.brands.filter((b) => b != title)
                            : [...filters.brands, title],
                        })
                      }
                    >
                      {x + i}
                    </span>
                  );
                })}
              </div>
              <DialogFooter>
                <DialogClose asChild>
                  <Button>Выбрать</Button>
                </DialogClose>
              </DialogFooter>
            </DialogContent>
          </Dialog>

          <Select>
            <SelectTrigger className=" ">
              <SelectValue placeholder="Любой тип топлива" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                onClick={() =>
                  setFilters({ ...filters, fuelType: FuelType.Gasoline })
                }
                value={FuelType.Gasoline}
              >
                Бензин
              </SelectItem>
              <SelectItem
                onClick={() =>
                  setFilters({ ...filters, fuelType: FuelType.Gas })
                }
                value={FuelType.Gas}
              >
                Газ
              </SelectItem>
              <SelectItem
                onClick={() => setFilters({ ...filters, fuelType: null })}
                value={null as any}
              >
                Любой тип топлива
              </SelectItem>
            </SelectContent>
          </Select>


          <Select>
            <SelectTrigger className=" ">
              <SelectValue placeholder="Любой тип трансмиссии" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                onClick={() =>
                  setFilters({ ...filters, transmissionType: TransmissionType.Automatic })
                }
                value={TransmissionType.Automatic}
              >
                Автомат
              </SelectItem>
              <SelectItem
                onClick={() =>
                  setFilters({ ...filters, transmissionType: TransmissionType.Mechanics })
                }
                value={TransmissionType.Mechanics}
              >
                Ручная
              </SelectItem>
              <SelectItem
                onClick={() => setFilters({ ...filters, transmissionType: null })}
                value={null as any}
              >
                Любой тип трансмиссии
              </SelectItem>
            </SelectContent>
          </Select>
          {/* <DropdownMenu>
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
                  <DropdownMenuItem key={title}
                    onClick={() =>
                      setFilters({ ...filters, fuelType })
                    }
                  >
                    {title}
                  </DropdownMenuItem>
                );
              })}
            </DropdownMenuContent>
          </DropdownMenu> */}
        </div>

        <div className="my-4 space-y-2">
          <Label>Комиссия парка не выше {filters.commission}%</Label>
          <Slider
            onValueChange={(e) => setFilters({ ...filters, commission: e[0] })}
            defaultValue={[DEFAULT_COMMISSION_PERCENTAGE]}
            max={10}
            step={0.1}
          />
        </div><Button variant="outline">Сбросить фильтры</Button>
        <Card />
        <Card />
        <Card />
        <Card />
      </div>
    </>
  );
};
