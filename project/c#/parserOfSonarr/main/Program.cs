using System;
using System.ComponentModel;
using NzbDrone.Core.Parser;

namespace Main
{
    class Program
    {
        static void Main(string[] args)
        {
            var result = Parser.ParseTitle(args[0]);
            if (result != null) {
                Console.WriteLine(result + "\n");
                Console.WriteLine("ParsedEpisodeInfo:");
                printModel(result, 3);
            } else {
                Console.WriteLine("Could not parse title");
                
            }
            
        }

        static void printModel<T>(T model, int depth = 2, int indent = 0)
        {
            if (depth == 0) return;

            var space = new String(' ', indent);

            foreach (PropertyDescriptor descriptor in TypeDescriptor.GetProperties(model))
            {
                string name = descriptor.Name;
                object value = descriptor.GetValue(model);
                if (value is int || value is string || value is bool ) {
                    Console.WriteLine("{0}{1} = {2}", space, name, value);
                } else {
                    if (value is Array) {
                        if (value is int[]) Console.WriteLine("{0}{1} = {2}", space, name, string.Join(",", (int[])value));
                        else if (value is decimal[]) Console.WriteLine("{0}{1} = {2}", space, name, string.Join(",", (decimal[])value));
                    } else {
                        Console.WriteLine("{0}{1} =", space, name);
                        printModel(value, depth - 1, indent + 2);
                    }
                }
            }
        }
    }
}
