import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MyFooter } from './my-footer';

describe('MyFooter', () => {
  let component: MyFooter;
  let fixture: ComponentFixture<MyFooter>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [MyFooter],
    }).compileComponents();

    fixture = TestBed.createComponent(MyFooter);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
