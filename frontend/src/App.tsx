import { useEffect } from "react";
import logo from "./assets/logo.png";
import "./App.css";
import { Link, Route, Routes } from "react-router-dom";
import { client } from "./backend";
import { Finder } from "./Finder";
import { Account } from "./Account";
import { DriverLogin } from "./DriverLogin";
import { useRecoilState } from "recoil";
import { userAtom } from "./atoms";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { Button } from "@/components/ui/button";
import {
  Drawer,
  DrawerClose,
  DrawerContent,
  DrawerDescription,
  DrawerFooter,
  DrawerHeader,
  DrawerTitle,
  DrawerTrigger,
} from "@/components/ui/drawer";
import {
  faRightFromBracket,
  faRightToBracket,
  faTaxi,
  faUser,
} from "@fortawesome/free-solid-svg-icons";
import { User } from "./api-client";
import { CityPicker } from "./CityPicker";
import { Separator } from "@/components/ui/separator";
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "@/components/ui/collapsible";
import { Badge } from "@/components/ui/badge";
import {
  formatRoubles,
  getFuelTypeDisplayName,
  getTransmissionDisplayName,
} from "@/lib/utils";

function App() {
  const [user, setUser] = useRecoilState(userAtom);
  const isBooked: boolean = true;

  useEffect(() => {
    const checkAuth = async () => {
      const token = localStorage.getItem("token");
      if (token) {
        (window as any).token = token;
        try {
          const userData = await client.getUser();

          setUser(userData.user!);
        } catch (error) {}
      }
    };

    checkAuth();
  }, []);

  return (
    <div className="max-w-sm p-4 mx-auto">
      <div className="flex justify-between my-0 space-x-2">
        <a>
          <img className="w-32" src={logo} alt="logo" />
        </a>
        <Menu user={user} />
        {/* <span className="font-bold text-md text-gray"></span> */}
        <CityPicker />
      </div>

      <Routes>
        <Route path="/" element={<Finder />} />
        <Route path="account" element={<Account user={user} />} />
        <Route path="login/driver" element={<DriverLogin />} />
        <Route path="login/manager" element={<ManagerLogin />} />
        <Route path="login/admin" element={<AdminLogin />} />
      </Routes>
      <BookingDrawer open={isBooked} />
    </div>
  );
}

export default App;
const BookingDrawer = ({ open }: { open: boolean }) => (
  <>
    <Drawer open={open}>
      <DrawerContent>
        <DrawerHeader>
          <DrawerTitle>Are you absolutely sure?</DrawerTitle>
          <DrawerDescription>This action cannot be undone.</DrawerDescription>
        </DrawerHeader>
        {/* <div className="flex flex-col justify-center py-8 overflow-y-auto "> */}
        {/* <img
            className="object-cover w-full rounded-t-xl"
            src={car.images![0]}
            alt=""
          />
          <div className="space-y-2">
            <h1 className="my-4 text-center">{`${car.brand} ${car.model} ${car.year_produced}`}</h1>

            <p className="text-base font-semibold text-gray">
              Парк: {car.park_name}
            </p>
            <Separator />
            <p className="text-base font-semibold text-gray">
              Адрес: {car.division?.address}
            </p>
            <Separator />
            <p className="text-base font-semibold text-gray">
              Телефон: {car.phone}
            </p>
            <Separator />
            <p className="text-base font-semibold text-gray">
              Минимум дней аренды: {car.rent_term?.minimum_period_days}
            </p>
            <br />
            <div className="min-h-48">
              {car.working_hours?.map((x) => (
                <div className="flex items-center" key={x.day}>
                  <div className="text-sm capitalize w-28">{x.day}</div>{" "}
                  {x.start} - {x.end}
                </div>
              ))}
            </div>
            <Collapsible>
              <CollapsibleTrigger>О парке ▼</CollapsibleTrigger>
              <CollapsibleContent>
                <div className="text-sm text-gray-700">{car.about}</div>
              </CollapsibleContent>
            </Collapsible>
          </div>

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
            <Badge variant="card">Комиссия {car.commission}</Badge>
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
          <div className="flex flex-wrap gap-1 pb-20 mb-16">
            {currentSchemas?.map((currentSchema, i) => (
              <Badge
                key={`${currentSchema.working_days}/${currentSchema.non_working_days}${i}`}
                className="flex-col items-start justify-start flex-grow h-full px-2 text-lg font-bold text-wrap"
                variant="schema"
              >
                {`${formatRoubles(currentSchema.daily_amount!)}`}
                <div className="text-xs font-medium text-black">{`${currentSchema.working_days}раб. /${currentSchema.non_working_days}вых.`}</div>
              </Badge>
            ))}
          </div>
        </div> */}
        {/* <div className="fixed bottom-0 left-0 flex justify-center w-full px-4 py-4 space-x-2 bg-white border-t border-pale"> */}
        {/* <Badge variant="schema" className="w-1/2 h-auto border-none bg-grey">
            {`${
              car.rent_term?.schemas![0]?.daily_amount !== undefined
                ? formatRoubles(car.rent_term?.schemas![0]!.daily_amount)
                : ""
            }`}
            <div className="text-xs font-medium text-black">{`${
              car.rent_term?.schemas![0]!.working_days
            }раб. /${car.rent_term?.schemas![0]!.non_working_days}вых.`}</div>
          </Badge>
          <Button onClick={book} className="w-1/2">
            Забронировать
          </Button> */}
        {/* </div> */}

        <DrawerFooter>
          <Button>Submit</Button>
          <DrawerClose>
            <Button variant="outline">Cancel</Button>
          </DrawerClose>
        </DrawerFooter>
      </DrawerContent>
    </Drawer>
  </>
);
const LogoutHandler = () => {
  client.logout();
  localStorage.clear();
  window.location.href = "/";
};

const Menu = ({ user }: { user: User }) => (
  <div className="flex mx-auto space-x-4 cursor-pointer justify-evenly w-60">
    <Link className="hover:text-sky-400" to="/">
      <FontAwesomeIcon icon={faTaxi} className="h-4" />
    </Link>
    <Link className="hover:text-sky-400" to={user ? "account" : "login/driver"}>
      <FontAwesomeIcon icon={faUser} className="h-4" />
    </Link>
    {user && (
      <div className="hover:text-sky-400" onClick={LogoutHandler}>
        <FontAwesomeIcon icon={faRightFromBracket} className="h-4" />
      </div>
    )}
    {!user && (
      <Link className="hover:text-sky-400" to="login/driver">
        <FontAwesomeIcon icon={faRightToBracket} className="h-4" />
      </Link>
    )}
  </div>
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
