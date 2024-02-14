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

export const ModalCard = () => (
  <>
<div className="flex justify-center space-x-4">
      <div className="w-80">
        <h1 className="text-center my-4">Skoda Octavia 2018</h1>
        <p className="text-base font-semibold text-gray">Парк:</p>
        <p className="text-base font-semibold text-gray">Адрес:</p>
        <p className="text-base font-semibold text-gray">
          Минимальный срок аренды:
        </p>
        <div className="text-center my-4">
          <Button variant="secondary">Показать номер</Button>
        </div>
        <div className="text-center">
          <Button>Забронировать</Button>
        </div>
        <div className="text-sm text-gray-700 mt-4">
          Такси ХИЩНИК – это таксопарк и опытная команда профессионалов,
        </div>
        <Collapsible>
          <CollapsibleTrigger>▼</CollapsibleTrigger>
          <CollapsibleContent>
            <div className="text-sm text-gray-700">
              занимающихся подключением к агрегаторам такси с марта 2018 года.
              За это время мы добились лучших условий работы с крупнейшими
              агрегаторами такси – Яндекс.Такси, Gett, Ситимобил, DiDi. А
              сегодня приглашаем к сотрудничеству водителей и готовы поручиться
              за Вас перед авторитетными сервисами. С нашей компанией Вы не
              будете знать простоев и научитесь зарабатывать много, сберегая
              своё время и силы.
            </div>
          </CollapsibleContent>
        </Collapsible>
      </div>

      <div className="w-80 flex flex-col items-center">
        <div className="mx-auto text-gray-700 bg-white shadow-md bg-clip-border rounded-xl">
          <img
            className="object-cover w-full h-full rounded-xl"
            src="https://garage.kwol.ru/assets/uploads/media-uploader/batmobile-batmobile-c578611042018230313-21695323571.jpg"
          />
        </div>
        <div className="flex flex-wrap justify-center items-center mt-4 space-x-2">
          <Badge variant="outline">Депозит</Badge>
          <Badge variant="outline">Комиссия</Badge>
          <Badge variant="outline">Бензин</Badge>
          <Badge variant="outline">Ручная</Badge>
          <Badge variant="outline">1650р. 7 раб./0 вых.</Badge>
        </div>
      </div>
    </div>
  </>
);

