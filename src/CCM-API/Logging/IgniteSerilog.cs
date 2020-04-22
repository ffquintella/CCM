using System;
using System.Collections.Concurrent;
using Apache.Ignite.Core.Log;
//using Microsoft.Extensions.Logging;
using Serilog;
using Serilog.Events;

namespace CCM_API.Logging
{
    public class IgniteSerilog : Apache.Ignite.Core.Log.ILogger
    {


        public void Log(LogLevel level, string message, object[] args, 
            IFormatProvider formatProvider, string category,
            string nativeErrorInfo, Exception ex)
        {

            LogEventLevel slevel;

            switch (level)
            {
                case LogLevel.Debug:
                    slevel = LogEventLevel.Debug;
                    break;
                case LogLevel.Error:
                    slevel = LogEventLevel.Error;
                    break;
                case LogLevel.Info:
                    slevel = LogEventLevel.Information;
                    break;
                case LogLevel.Warn:
                    slevel = LogEventLevel.Warning;
                    break;
                case LogLevel.Trace:
                    slevel = LogEventLevel.Verbose;
                    break;
                default:
                    slevel = LogEventLevel.Information;
                    break;
            }
            
            Serilog.Log.Write(slevel, message, args);
        }
        public bool IsEnabled(LogLevel level)
        {
            // Accept any level.
            return true;
        }
    }
}