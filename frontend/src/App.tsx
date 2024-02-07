import { useState } from "react";
import reactLogo from "./assets/react.svg";
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
      <div
        className="w-1/2 h-80 mx-auto flex flex-col
   items-center"
      >
        {/* <img className='animate-spin' src={reactLogo} /> */}
        <Menu />
        <Routes>
          <Route path="login/driver" element={<DriverLogin />} />
          <Route path="login/manager" element={<ManagerLogin />} />
          <Route path="login/admin" element={<AdminLogin />} />
        </Routes>
      </div>
      <Outlet />
    </>
  );
}

export default App;

const DriverLogin = () => (
  <>
    <div className=" ">
      <h1>Driver login page</h1>

      <Label htmlFor="email">Email</Label>
      <Input type="email" id="email" placeholder="mya@ya.ru" />

      <Label htmlFor="email">Phone</Label>
      <Input title="phone" placeholder="+7 (846) 429-383-645" />

      <Button type="button">Войти</Button>
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
