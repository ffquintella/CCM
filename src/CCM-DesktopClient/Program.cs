using WebWindows.Blazor;
using System;

namespace BlazorDesktopClient
{
    public class Program
    {
        static void Main(string[] args)
        {
            //ComponentsDesktop.Run<Startup>("CCM Client", "wwwroot/login.html");
            ComponentsDesktop.Run<Startup>("CCM Client", "wwwroot/index.html");
        }
    }
}
