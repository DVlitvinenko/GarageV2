import { Button } from "@/components/ui/button";
import econom from "./assets/car_icons/econom.png";
import comfort from "./assets/car_icons/comfort.png";
import comfortPlus from "./assets/car_icons/comfort-plus.png";
import business from "./assets/car_icons/business.png";
import { Checkbox } from "@/components/ui/checkbox";

import { useEffect, useState } from "react";
import {
  Body9,
  CarClass,
  Cars2,
  FuelType,
  Schemas,
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
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Card } from "./Card";
import { client } from "./backend";
import {
  getFuelTypeDisplayName,
  getTransmissionDisplayName,
} from "@/lib/utils";
import { useDebouncedCallback } from "use-debounce";
import { type } from "os";
import { useRecoilState, useRecoilValue } from "recoil";
import { cityAtom } from "./atoms";
import { Badge } from "@/components/ui/badge";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faArrowRightArrowLeft } from "@fortawesome/free-solid-svg-icons";

const DEFAULT_COMMISSION_PERCENTAGE = 0;

type CarFilter = {
  brands: string[];
  carClass: CarClass[];
  commission: number;
  fuelType: FuelType | null;
  transmissionType: TransmissionType | null;
  selfEmployed: boolean;
  buyoutPossible: boolean;
  rentTerm: Schemas | null;
  sorting: "asc" | "desc";
};

enum ActiveFilter {
  FuelType,
  TransmissionType,
  RentTerm,
  Sorting,
}

const staticSchemas = [
  new Schemas({ working_days: 7, non_working_days: 0 }),
  new Schemas({ working_days: 6, non_working_days: 1 }),
  new Schemas({ working_days: 14, non_working_days: 1 }),
];

export const Finder = () => {
  const [filters, setFilters] = useState<CarFilter>({
    carClass: [CarClass.Economy],
    commission: DEFAULT_COMMISSION_PERCENTAGE,
    fuelType: null,
    brands: [],
    transmissionType: null,
    selfEmployed: false,
    buyoutPossible: false,
    sorting: "asc",
    rentTerm: null,
  });

  const [cars, setCars] = useState<Cars2[]>([]);
  const [activeFilter, setActiveFilter] = useState<ActiveFilter>(
    ActiveFilter.Sorting
  );

  const city = useRecoilValue(cityAtom);

  useEffect(() => {
    const getCars = async () => {
      const data = await client.searchCars(
        new Body9({
          brand: filters.brands,
          city,
          fuel_type: filters.fuelType || undefined,
          transmission_type: filters.transmissionType || undefined,
          car_class: filters.carClass,
          limit: 50,
          offset: 0,
          sorting: filters.sorting,
          commission: filters.commission,
          self_employed: filters.selfEmployed,
          is_buyout_possible: filters.buyoutPossible,
        })
      );

      setCars(data.cars!);
    };

    getCars();
  }, [filters, city]);

  const debouncedCommission = useDebouncedCallback((value) => {
    setFilters({ ...filters, commission: value });
  }, 300);

  return (
    <>
      {/* <div onClick={() => navigate("login/driver")} className="fixed top-5 right-5">Войти</div> */}
      <div className="">
        <div className="flex justify-between h-24 mx-auto my-2">
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
                className={`w-20 flex flex-col items-center bg-white rounded-xl transition-all ${
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
        <div className="flex justify-between my-4 space-x-4 overflow-x-auto">
          {[
            {
              title: (
                <FontAwesomeIcon
                  icon={faArrowRightArrowLeft}
                  className="rotate-90"
                />
              ),
              filter: ActiveFilter.Sorting,
            },
            {
              title: "Любой график аренды",
              filter: ActiveFilter.RentTerm,
            },
            {
              title: "Трансмиссия",
              filter: ActiveFilter.TransmissionType,
            },
            {
              title: "Топливо",
              filter: ActiveFilter.FuelType,
            },
          ].map(({ filter, title }) => (
            <Badge
              className={`${activeFilter === filter ? "bg-white" : ""} `}
              onClick={() => setActiveFilter(filter)}
            >
              {title}
            </Badge>
          ))}
          <Dialog>
            <DialogTrigger asChild>
              <Button variant="outline">
                {filters.brands.length
                  ? filters.brands.join(", ")
                  : "Все марки"}
              </Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-[800px]">
              <DialogHeader>
                <DialogTitle>Марка автомобиля</DialogTitle>
              </DialogHeader>
              <div className="grid grid-cols-3 gap-4 py-4 h-[300px] overflow-y-scroll">
                {["Audi", "BMW", "Kia", "Hyundai"].map((x) => {
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
        </div>
        <div className="">
          {activeFilter === ActiveFilter.Sorting &&
            ["asc", "desc"].map((sorting) => (
              <Badge
                className={`${filters.sorting === sorting ? "bg-white" : ""} `}
                onClick={() =>
                  setFilters({
                    ...filters,
                    sorting: sorting as CarFilter["sorting"],
                  })
                }
              >
                {sorting === "asc" && "Сначала самые дешевые"}
                {sorting === "desc" && "Сначала самые дорогие"}
              </Badge>
            ))}
          {activeFilter === ActiveFilter.RentTerm &&
            [null, ...staticSchemas].map((rentTerm) => (
              <Badge
                className={`${
                  filters.rentTerm === rentTerm ? "bg-white" : ""
                } `}
                onClick={() =>
                  setFilters({
                    ...filters,
                    rentTerm,
                  })
                }
              >
                {rentTerm
                  ? `${rentTerm?.working_days}/${rentTerm?.non_working_days}`
                  : "Любой график аренды"}
              </Badge>
            ))}
          {activeFilter === ActiveFilter.TransmissionType &&
            [TransmissionType.Automatic, TransmissionType.Mechanics, null].map(
              (transmissionType) => (
                <Badge
                  className={`${
                    filters.transmissionType === transmissionType
                      ? "bg-white"
                      : ""
                  } `}
                  onClick={() =>
                    setFilters({
                      ...filters,
                      transmissionType,
                    })
                  }
                >
                  {getTransmissionDisplayName(transmissionType)}
                </Badge>
              )
            )}
          {activeFilter === ActiveFilter.FuelType &&
            [FuelType.Gasoline, FuelType.Gas, null].map((fuelType) => (
              <Badge
                className={`${
                  filters.fuelType === fuelType ? "bg-white" : ""
                } `}
                onClick={() =>
                  setFilters({
                    ...filters,
                    fuelType,
                  })
                }
              >
                {getFuelTypeDisplayName(fuelType)}
              </Badge>
            ))}
        </div>
        {/* <div className="pb-8 my-4 mb-4 space-y-2 border-b border-gray/20">
          <Label>Комиссия парка не выше {filters.commission}%</Label>
          <Slider
            onValueChange={(e) => debouncedCommission(e[0])}
            defaultValue={[DEFAULT_COMMISSION_PERCENTAGE]}
            max={10}
            step={0.1}
          />
        </div> */}
        {/* <Button variant="outline">Сбросить фильтры</Button> */}
        {cars.map((car) => {
          return <Card key={car.id} car={car} />;
        })}
      </div>
    </>
  );
};
