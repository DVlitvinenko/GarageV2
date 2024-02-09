import { useState } from "react";
import logo from "./assets/logo.png";
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
      <img className="my-8 mx-auto" src={logo} />
      {/* <Menu /> */}
      <Routes>
        <Route path="/" element={<DriverLogin />} />
        <Route path="login/manager" element={<ManagerLogin />} />
        <Route path="login/admin" element={<AdminLogin />} />
      </Routes>
    </>
  );
}

export default App;

const DriverLogin = () => (
  <>
    <div className="w-80 mx-auto">
      <h1  className="text-center my-10">
        Зарегистрируйтесь, чтобы получить возможность бронирования автомобиля
        или войдите в личный кабинет.
      </h1>
      <Label htmlFor="email">Введите ваш телефон</Label>
      <Input title="phone" placeholder="+7 (999) 123-45-67" />

      <Label htmlFor="email">Введите полученную смс</Label>
      <Input type="email" id="email" placeholder="_ _ _ _ _ _ _" />
      <p  className="text-center my-4 text-red">
        Вы ввели неправильный код
      </p>
      <div className="text-center">
        <Button type="button">Войти</Button>
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
  <div className="space-x-4 font-bold text-sm mb-12 cursor-pointer my-8">
    <Link className="hover:text-sky-400" to="login/driver">
      Водитель
    </Link>
    <Link className="hover:text-sky-400" to="login/manager">
      Менеджер
    </Link>
    <Link className="hover:text-sky-400" to="login/admin">
      Админ
    </Link>
  </div>
);
