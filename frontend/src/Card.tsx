import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
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
} from "@/components/ui/dialog";
import { ModalCard } from "./ModalCard";

export const Card = ({ car }: { car: Cars2 }) => {
  const currentSchema = car.rent_term!.schemas!.sort(
    (a, b) => a.daily_amount! - b.daily_amount!
  )[0];

  return (
    <div className="pb-4 mx-auto mb-8 text-gray-700 bg-white shadow-md w-80 rounded-xl">
      <div>
        <div className="absolute z-50 p-2 font-medium rounded-tl-lg rounded-br-lg shadow text-gray bg-yellow">
          {car.park_name}
        </div>
        <img
          className="object-cover w-full h-52 rounded-t-xl"
          src={car.images![0]!}
        />
      </div>
      <div className="px-4">
        <h1 className="my-4 text-center">{`${car.brand} ${car.model} ${car.year_produced}`}</h1>
        <div className="flex flex-col items-center mx-auto space-y-2">
          <Badge
            className="mb-2 text-lg font-semibold"
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
        <div className="mt-4 text-center">
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
