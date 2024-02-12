import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Checkbox } from "@/components/ui/checkbox";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

export const CarFinder = () => (
  <>
    <div className="mx-auto w-80">
      <h2 className="my-10 text-center">ПОИСК АВТО</h2>
      <Checkbox title="Для самозанятых" />
      <Checkbox title="Выкуп автомобиля" />
      <DropdownMenu>
        <DropdownMenuTrigger>Сначала дешевые</DropdownMenuTrigger>
        <DropdownMenuContent>
          <DropdownMenuItem>Сначала дорогие</DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
      <DropdownMenu>
        <DropdownMenuTrigger>Марка авто</DropdownMenuTrigger>
        <DropdownMenuContent>
          {/* <DropdownMenuLabel>BMW</DropdownMenuLabel> */}
          {/* <DropdownMenuSeparator /> */}
          <DropdownMenuItem>BMW</DropdownMenuItem>
          <DropdownMenuItem>Lada</DropdownMenuItem>
          <DropdownMenuItem>Chery</DropdownMenuItem>
          <DropdownMenuItem>Geely</DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
      <DropdownMenu>
        <DropdownMenuTrigger>Любой тип топлива</DropdownMenuTrigger>
        <DropdownMenuContent>
          <DropdownMenuItem>Бензин</DropdownMenuItem>
          <DropdownMenuItem>Газ</DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
      <DropdownMenu>
        <DropdownMenuTrigger>Комиссия парка</DropdownMenuTrigger>
        <DropdownMenuContent>
          <DropdownMenuItem>&lt; 3</DropdownMenuItem>
          <DropdownMenuItem>&lt; 5</DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
      <p className="my-4 text-center text-red">Вы ввели неправильный код</p>
      <div className="text-center">
        <Button>Войти</Button>
      </div>
    </div>
  </>
);
