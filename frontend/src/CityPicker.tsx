import { useState } from "react";
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
} from "@/components/ui/command";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";

import { Check } from "lucide-react";
import { cn } from "@/lib/utils";
import { useRecoilState } from "recoil";
import { cityAtom } from "./atoms";
import allCitiesIndistinct from "../../backend/public/cities.json";
import { uniq } from "ramda";

const allCities = uniq(allCitiesIndistinct);

export function CityPicker() {
  const [open, setOpen] = useState(false);
  const [city, setCity] = useRecoilState(cityAtom);

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        {/* <Button
          variant="outline"
          role="combobox"
          aria-expanded={open}
          className="justify-between w-full"
        >
          {city}
          <ChevronsUpDown className="w-4 h-4 ml-2 opacity-50 shrink-0" />
        </Button> */}
        <div className=" flex items-center w-48 capitalize text-[12px] font-bold justify-end sm:text-xl sm:w-96">
          {city}
        </div>
      </PopoverTrigger>
      <PopoverContent className="w-[200px] p-0">
        <Command>
          <CommandInput placeholder={city} />
          <CommandEmpty></CommandEmpty>
          <CommandGroup>
            {allCities.map((c: string) => (
              <CommandItem
                key={c}
                value={c}
                onSelect={(ccc) => {
                  setCity(ccc);
                  setOpen(false);
                }}
              >
                <Check
                  className={cn(
                    "mr-2 h-4 w-4",
                    city === c ? "opacity-100" : "opacity-0"
                  )}
                />
                {c}
              </CommandItem>
            ))}
          </CommandGroup>
        </Command>
      </PopoverContent>
    </Popover>
  );
}
