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

export const Card = () => (
  <div className="w-80 mx-auto text-gray-700 bg-white shadow-md rounded-xl">
    <div >
      <img
        className="object-cover w-full h-full rounded-xl"
        src="https://garage.kwol.ru/assets/uploads/media-uploader/batmobile-batmobile-c578611042018230313-21695323571.jpg"
      />
    </div>
    <div className="items-center w-80 mx-auto">
      <h1 className="text-center my-4">Skoda Octavia 2018</h1>
      {/* <div className="text-center my-4">
        <Button variant="secondary">Показать номер</Button>
      </div> */}
      {/* <p className="text-base font-semibold text-gray">Парк:</p>
      <p className="text-base font-semibold text-gray">Адрес:</p>
      <p className="text-base font-semibold text-gray">График работы парка:</p>
      <div className="text-sm text-gray-700">9:00 - 17:00 </div>
      <div className="text-sm text-gray-700">9:00 - 17:00</div>
      <div className="text-sm text-gray-700">9:00 - 17:00</div>
      <div className="text-sm text-gray-700">9:00 - 17:00</div>
      <div className="text-sm text-gray-700">9:00 - 17:00</div>
      <div className="text-sm text-gray-700">9:00 - 17:00</div>
      <div className="text-sm text-gray-700">9:00 - 17:00</div> */}
      {/* <div className="text-sm text-gray-700">
        "Такси ХИЩНИК – это таксопарк и опытная команда профессионалов,
        занимающихся подключением к агрегаторам такси с марта 2018 года. За это
        время мы добились лучших условий работы с крупнейшими агрегаторами такси
        – Яндекс.Такси, Gett, Ситимобил, DiDi. А сегодня приглашаем к
        сотрудничеству водителей и готовы поручиться за Вас перед авторитетными
        сервисами. С нашей компанией Вы не будете знать простоев и научитесь
        зарабатывать много, сберегая своё время и силы."
      </div> */}
      <div className="flex flex-col items-center w-80 h-44 mx-auto p-4 my-4 bg-grey shadow-md rounded-xl border border-grey">
      <Badge variant="outline"><img
            className="w-4 h-4 rounded-sm "
            src="https://garage.kwol.ru/assets/uploads/media-uploader/batmobile-batmobile-c578611042018230313-21695323571.jpg"
          />Депозит</Badge>
          <Badge variant="outline"><img
            className="w-4 h-4 rounded-sm "
            src="https://garage.kwol.ru/assets/uploads/media-uploader/batmobile-batmobile-c578611042018230313-21695323571.jpg"
          />Комиссия</Badge>
        <Badge variant="outline"><img
            className="w-4 h-4 rounded-sm "
            src="https://garage.kwol.ru/assets/uploads/media-uploader/batmobile-batmobile-c578611042018230313-21695323571.jpg"
          />Бензин</Badge>
        <Badge variant="outline"><img
            className="w-4 h-4 rounded-sm "
            src="https://garage.kwol.ru/assets/uploads/media-uploader/batmobile-batmobile-c578611042018230313-21695323571.jpg"
          />Ручная</Badge>
          <Badge variant="outline">1650р. 7 раб./0 вых.</Badge>
      </div>
      <div className="text-center">
        <Button>Подробнее</Button>
      </div>
    </div>
    </div>
);
