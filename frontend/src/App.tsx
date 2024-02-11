import { useState } from "react";
import logo from "./assets/logo.png";
import selca from "./assets/placeholders/selca.png";
import frontdriverid from "./assets/placeholders/front-driver-id.png";
import backdriverid from "./assets/placeholders/back-driver-id.png";
import frontpassport from "./assets/placeholders/front-passport.png";
import backpassport from "./assets/placeholders/back-passport.png";
import search from "./assets/search.svg";
import account from "./assets/account.svg";
import viteLogo from "/vite.svg";
import "./App.css";
import { Link, Outlet, Route, Routes } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

function App() {
  const [count, setCount] = useState(0);

  return (
    <>
      <a>
        <img className="my-8 mx-auto" src={logo} />
      </a>
      <Menu />
      <Routes>
        <Route path="/" element={<DriverLogin />} />
        <Route path="/search" element={<CarFinder />} />
        <Route path="/account" element={<Account />} />
        <Route path="login/manager" element={<ManagerLogin />} />
        <Route path="login/admin" element={<AdminLogin />} />
      </Routes>
    </>
  );
}

export default App;

const CarFinder = () => (
  <>
    <div className="w-80 mx-auto">
      <h2 className="text-center my-10">ПОИСК АВТО</h2>
      <Label htmlFor="email">Введите ваш телефон</Label>
      <Input title="phone" placeholder="+7 (999) 123-45-67" />

      <Label htmlFor="email">Введите полученную смс</Label>
      <Input type="email" id="email" placeholder="_ _ _ _ _ _ _" />
      <p className="text-center my-4 text-red">Вы ввели неправильный код</p>
      <div className="text-center">
        <Button>Войти</Button>
      </div>
    </div>
  </>
);

const DriverLogin = () => (
  <>
    <div className="w-80 mx-auto">
      <h2 className="text-center my-10">
        Зарегистрируйтесь, чтобы получить возможность бронирования автомобиля
        или войдите в личный кабинет.
      </h2>
      <Label htmlFor="email">Введите ваш телефон</Label>
      <Input title="phone" placeholder="+7 (999) 123-45-67" />

      <Label htmlFor="email">Введите полученную смс</Label>
      <Input type="email" id="email" placeholder="_ _ _ _ _ _ _" />
      <p className="text-center my-4 text-red">Вы ввели неправильный код</p>
      <div className="text-center">
        <Button>Войти</Button>
      </div>
      <div className="text-center my-4">
        Нажимая 'Войти' вы соглашаетесь с{" "}
        <a className="text-blue-800 underline" href="kwol.ru">
          условиями договора
        </a>
      </div>
    </div>
  </>
);
const Account = () => (
  <>
    <div className="w-80 mx-auto">
      <p className="text-center text-red">
        Вы не можете начать процесс бронирование пока не загрузили документы или
        документы не прошли верификацию
      </p>
      <h1 className="text-center mt-8">Подтвердите свою личность</h1>
      <p className="text-center my-4">
        Загрузите селфи при хорошем освещении c главным разворотом страниц
        паспорта
      </p>
      <img className="my-8 mx-auto" src={selca} />
      <div className="text-center">
        <Button>Загрузить</Button>
      </div>
      <h1 className="text-center mt-8">Регистрация документов</h1>
      <p className="text-center my-4">
        Загрузите лицевую сторону водительского удостоверения
      </p>
      <img className="my-8 mx-auto" src={frontdriverid} />
      <div className="text-center">
        <Button>Загрузить</Button>
      </div>
      {/* <h1 className="text-center mt-8 mt-4">Регистрация документов</h1> */}
      <p className="text-center my-4">
        Загрузите обратную сторону водительского удостоверения
      </p>
      <img className="my-8 mx-auto" src={backdriverid} />
      <div className="text-center">
        <Button>Загрузить</Button>
      </div>
      {/* <h1 className="text-center mt-8 mt-4">Регистрация документов</h1> */}
      <p className="text-center my-4">
        Загрузите разворот вашего паспорта с фото
      </p>
      <img className="my-8 mx-auto" src={frontpassport} />
      <div className="text-center">
        <Button>Загрузить</Button>
      </div>
      {/* <h1 className="text-center mt-8 mt-4">Регистрация документов</h1> */}
      <p className="text-center my-4">
        Загрузите страницу паспорта с разворотом прописки
      </p>
      <img className="my-8 mx-auto" src={backpassport} />
      <div className="text-center">
        <Button>Загрузить</Button>
      </div>
      <div className="text-center my-8">
        <Button>Y</Button>
      </div>
      <div className="text-center my-8">
        <Button variant="reject">Отменить</Button>
      </div>
    </div>
  </>
);

const ManagerLogin = () => (
  <>
    <h1>Manager login page</h1>
  </>
);
const AdminLogin = () => (
  <>
    <h1>Admin login page</h1>
  </>
);

const Menu = () => (
  <div className="w-60 mx-auto flex justify-around mb-12 cursor-pointer my-2">
    <Link className="hover:text-sky-400" to="/search">
      <img className="object-scale-down h-8 w-8" src={search} />
    </Link>
    <Link className="hover:text-sky-400" to="account">
      <img className="object-scale-down h-8 w-8" src={account} />
    </Link>
  </div>
);
