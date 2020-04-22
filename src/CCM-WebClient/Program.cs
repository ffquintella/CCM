using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Threading.Tasks;
using Microsoft.AspNetCore;
using Microsoft.AspNetCore.Hosting;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Hosting;
using Microsoft.Extensions.Logging;
using RestSharp;
using Serilog;

namespace CCM_WebClient
{
    public class Program
    {
        public static void Main(string[] args)
        {

            var loggerConfiguration = new LoggerConfiguration()
                .MinimumLevel.Debug()
                .WriteTo.Console()
                .WriteTo.File("logs/ccm-webclient.log", rollingInterval: RollingInterval.Day);
                
            
            var restClientAutologConfiguration = new RestClientAutologConfiguration()
            {
                MessageTemplateForSuccess = "{Method} {Uri} responded {StatusCode}", 
                MessageTemplateForError = "{Method} {Uri} is not good! {ErrorMessage}", 
                LoggerConfiguration = loggerConfiguration
            };

            Log.Logger = loggerConfiguration.CreateLogger();
            
            
            try
            {
                Log.Information("Starting up");
                CreateHostBuilder(args).Build().Run();
            }
            catch (Exception ex)
            {
                Log.Fatal(ex, "Application start-up failed");
            }
            finally
            {
                Log.CloseAndFlush();
            }
        }

        public static IHostBuilder CreateHostBuilder(string[] args) =>
            Host.CreateDefaultBuilder(args)
                .UseSerilog()
                .ConfigureWebHostDefaults(webBuilder => { webBuilder.UseStartup<Startup>(); });
    }
}