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
import { useEffect, useState } from "react";
import {
  Body9,
  CarClass,
  Cars,
  Cars2,
  FuelType,
  TransmissionType,
} from "./api-client";
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
import { useNavigate } from "react-router-dom";
import { Card } from "./Card";
import { client } from "./backend";
import {
  getFuelTypeDisplayName,
  getTransmissionDisplayName,
} from "@/lib/utils";
import { useDebouncedCallback } from "use-debounce";

const DEFAULT_COMMISSION_PERCENTAGE = 4;

export const Finder = () => {
  const [filters, setFilters] = useState<{
    brands: string[];
    carClass: CarClass[];
    commission: number;
    fuelType: FuelType | null;
    transmissionType: TransmissionType | null;
    city: string;
  }>({
    carClass: [CarClass.Economy],
    commission: DEFAULT_COMMISSION_PERCENTAGE,
    fuelType: null,
    brands: [],
    transmissionType: null,
    city: "Москва",
  });

  const [cars, setCars] = useState<Cars2[]>([]);

  useEffect(() => {
    const getCars = async () => {
      // const data = await client.searchCars(
      //   0,
      //   filters.city,
      //   "BMW"
      //   // 50,
      //   // filters.fuelType || undefined,
      //   // filters.transmissionType || undefined,
      //   // filters.brands[0],
      //   // undefined,
      //   // undefined,
      //   // filters.carClass[0]
      // );

      const data = await client.searchCars(
        new Body9({
          brand: filters.brands,
          city: filters.city,
          fuel_type: filters.fuelType || undefined,
          transmission_type: filters.transmissionType || undefined,
          car_class: filters.carClass,
          limit: 50,
          offset: 0,
          sorting: "asc"
        })
      );

      setCars(data.cars!);
    };

    getCars();
  }, [filters]);

  const navigate = useNavigate();

  const debouncedCommission = useDebouncedCallback(
    // function
    (value) => {
      setFilters({ ...filters, commission: value });
    },
    // delay in ms
    300
  );

  return (
    <>
      {/* <div onClick={() => navigate("login/driver")} className="fixed top-5 right-5">Войти</div> */}
      <div className="">
        <div className="mx-auto flex my-2 justify-between h-24">
          {[
            [CarClass.Economy, econom, "Эконом"],
            [CarClass.Comfort, comfort, "Комфорт"],
            [CarClass.ComfortPlus, comfortPlus, "Комфорт+"],
            [CarClass.Business, business, "Бизнес"],
          ].map((x) => {
            const [carClass, img, title] = x;
            const isActive = filters.carClass.includes(carClass as CarClass);

            return (
              <div
                key={carClass}
                className={`w-20 flex flex-col items-center bg-white rounded-xl ${
                  isActive ? "shadow border-2 border-yellow" : " scale-75"
                }`}
              >
                <img
                  className="w-16 rounded-xl"
                  onClick={() =>
                    setFilters({
                      ...filters,
                      carClass: isActive
                        ? filters.carClass.filter((c) => c != carClass)
                        : [...filters.carClass, carClass as CarClass],
                    })
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
                  ? filters.brands.join(", ")
                  : // "Выбрано марок " + filters.brands.length
                    "Все марки"}
              </Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-[800px]">
              <DialogHeader>
                <DialogTitle>Марка автомобиля</DialogTitle>
                {/* <DialogDescription>DialogDescription</DialogDescription> */}
              </DialogHeader>
              <div className="grid grid-cols-3 gap-4 py-4 h-[300px] overflow-y-scroll">
                {["Audi", "BWM"].map((x) => {
                  const title = x;
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
                      {title}
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

          <Select
            onValueChange={(e) =>
              setFilters({ ...filters, fuelType: e as FuelType })
            }
          >
            <SelectTrigger className=" ">
              <SelectValue placeholder={getFuelTypeDisplayName(undefined)} />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value={FuelType.Gasoline}>
                {getFuelTypeDisplayName(FuelType.Gasoline)}
              </SelectItem>
              <SelectItem value={FuelType.Gas}>
                {getFuelTypeDisplayName(FuelType.Gas)}
              </SelectItem>
              <SelectItem value={null as any}>
                {getFuelTypeDisplayName(undefined)}
              </SelectItem>
            </SelectContent>
          </Select>

          <Select
            onValueChange={(e) => {
              return setFilters({
                ...filters,
                transmissionType: e as TransmissionType,
              });
            }}
          >
            <SelectTrigger className=" ">
              <SelectValue placeholder="Любой тип трансмиссии" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value={TransmissionType.Automatic}>
                {getTransmissionDisplayName(TransmissionType.Automatic)}
              </SelectItem>
              <SelectItem value={TransmissionType.Mechanics}>
                {getTransmissionDisplayName(TransmissionType.Mechanics)}
              </SelectItem>
              <SelectItem value={null as any}>
                {getTransmissionDisplayName(undefined)}
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

        <div className="my-4 space-y-2 mb-4 border-b pb-8 border-gray/20">
          <Label>Комиссия парка не выше {filters.commission}%</Label>
          <Slider
            onValueChange={(e) => debouncedCommission(e[0])}
            defaultValue={[DEFAULT_COMMISSION_PERCENTAGE]}
            max={10}
            step={0.1}
          />
        </div>
        {/* <Button variant="outline">Сбросить фильтры</Button> */}
        {cars.map((car) => {
          return <Card key={car.id} car={car} />;
        })}
      </div>
    </>
  );
};
