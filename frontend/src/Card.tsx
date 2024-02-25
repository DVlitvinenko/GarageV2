import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Cars2 } from "./api-client";
import {
  formatRoubles,
  getFuelTypeDisplayName,
  getTransmissionDisplayName,
} from "@/lib/utils";
import { Dialog, DialogTrigger, DialogContent } from "@/components/ui/dialog";
import { ModalCard } from "./ModalCard";

export const Card = ({ car }: { car: Cars2 }) => {
  const currentSchemas = car.rent_term!.schemas!.sort(
    (a, b) => a.daily_amount! - b.daily_amount!
  );

  return (
    <Dialog>
      <DialogTrigger asChild>
        <div className="p-1 pb-4 mx-auto mb-8 text-gray-700 bg-white shadow-md w-100 rounded-xl">
          <div>
            <div className="absolute z-50 p-2 font-medium rounded-tl-lg rounded-br-lg shadow text-gray bg-yellow">
              {car.park_name}
            </div>
            <div className="flex space-x-1 overflow-x-auto scrollbar-hide rounded-xl">
              {car.images?.map((x, i) => (
                <img
                  key={`${x}${i}`}
                  className="object-cover w-10/12 rounded-sm h-52"
                  src={x}
                />
              ))}
            </div>
          </div>
          <div className="px-4">
            <h1 className="my-2 text-center">{`${car.brand} ${car.model} ${car.year_produced}`}</h1>
            <div className="flex flex-wrap items-center justify-start gap-1 mb-3">
              <Badge variant="card" className="px-0 py-0 bg-grey ">
                <span className="flex items-center h-full px-2 bg-white rounded-xl">
                  Депозит {formatRoubles(car.rent_term?.deposit_amount_total!)}
                </span>
                <span className="flex items-center h-full px-2 ">
                  {formatRoubles(car.rent_term?.deposit_amount_daily!)}
                  /день
                </span>
              </Badge>
              <Badge variant="card">Комиссия {car.commission}%</Badge>

              <Badge variant="card">
                {getFuelTypeDisplayName(car.fuel_type)}
              </Badge>

              <Badge variant="card">
                {getTransmissionDisplayName(car.transmission_type)}
              </Badge>

              {!!car.self_employed && (
                <Badge variant="card">Для самозанятых</Badge>
              )}
              {!!car.rent_term?.is_buyout_possible && (
                <Badge variant="card">Выкуп автомобиля</Badge>
              )}
            </div>
            <div className="flex flex-wrap gap-1">
              {currentSchemas?.slice(0, 3).map((currentSchema, i) => (
                <Badge
                  key={`${currentSchema.working_days}/${currentSchema.non_working_days}${i}`}
                  className=""
                  variant="schema"
                >
                  {`${formatRoubles(currentSchema.daily_amount!)}`}
                  <div className="text-xs font-medium text-black">{`${currentSchema.working_days}раб. /${currentSchema.non_working_days}вых.`}</div>
                </Badge>
              ))}
            </div>
            <div className="mt-4 text-center">
              <Button className="w-full sm:max-w-[376px]">Подробнее</Button>
            </div>
          </div>
        </div>
      </DialogTrigger>
      <DialogContent className="sm:max-w-[800px]">
        {/* <DialogHeader>
        <DialogTitle></DialogTitle> */}
        {/* <DialogDescription>DialogDescription</DialogDescription> */}
        {/* </DialogHeader> */}
        <ModalCard car={car} />
        {/* <DialogFooter>
        <DialogClose asChild>
          <Button>Выбрать</Button>
        </DialogClose>
      </DialogFooter> */}
      </DialogContent>
    </Dialog>
  );
};
