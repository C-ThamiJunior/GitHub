package com.example.demo;

import org.springframework.context.annotation.Configuration;
import org.springframework.format.FormatterRegistry;
import org.springframework.web.servlet.config.annotation.WebMvcConfigurer;

import com.example.demo.converter.StringToLocalDateConverter;
import com.example.demo.converter.StringToLocalTimeConverter;

@Configuration
public class WebConfig implements WebMvcConfigurer {

    private final StringToLocalDateConverter stringToLocalDateConverter;
    private final StringToLocalTimeConverter stringToLocalTimeConverter;

    public WebConfig(StringToLocalDateConverter stringToLocalDateConverter, StringToLocalTimeConverter stringToLocalTimeConverter) {
        this.stringToLocalDateConverter = stringToLocalDateConverter;
        this.stringToLocalTimeConverter = stringToLocalTimeConverter;
    }

    @Override
    public void addFormatters(@SuppressWarnings("null") FormatterRegistry registry) {
        registry.addConverter(stringToLocalDateConverter);
        registry.addConverter(stringToLocalTimeConverter);
    }
}

